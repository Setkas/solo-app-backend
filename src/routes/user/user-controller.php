<?php

use Commons\Authorization\Auth;
use Medoo\Medoo;
use Commons\MysqlLock;
use Moment\Moment;

class userController {
  /**
   * Encoded columns in database
   *
   * @var array
   */
  private $eCols = [
    'title',
    'name',
    'surname'
  ];

  /**
   * Gets user data from database
   *
   * @param $practiceId
   * @param $userId
   * @return bool|mixed
   */
  public function loadUser($practiceId, $userId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('user', "*", [
      "id" => $userId,
      "practice_id" => $practiceId,
      "deleted" => null,
      "LIMIT" => [
        0,
        1
      ]
    ]);

    if ($result === false || count($result) === 0) {
      return false;
    }

    $user = MysqlLock\MysqlLock::DecodeRow($result[0]);

    unset($user["password"]);

    return $user;
  }

  /**
   * Check if user has practice master permissions
   *
   * @param $userId
   * @return bool
   */
  public function isMasterUser($userId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('user', "*", [
      "id" => $userId,
      "code" => 1,
      "LIMIT" => [
        0,
        1
      ]
    ]);

    return !($result === false || count($result) === 0);
  }

  /**
   * Loads all practice users
   *
   * @param $practiceId
   * @return array|bool
   */
  public function loadUsers($practiceId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('user', "*", [
      "practice_id" => $practiceId,
      "deleted" => null
    ]);

    if ($result === false || count($result) === 0) {
      return false;
    }

    $users = MysqlLock\MysqlLock::DecodeBatch($result);

    foreach ($users as $k => $u) {
      unset($users[$k]["password"]);
    }

    return $users;
  }

  /**
   * Encodes data for database use
   *
   * @param $data
   * @return array
   */
  private function encodeData($data) {
    if (!is_array($data)) {
      return $data;
    }

    foreach ($this->eCols as $col) {
      if (isset($data[$col])) {
        $data['e_' . $col] = $data[$col];

        unset($data[$col]);
      }
    }

    return MysqlLock\MysqlLock::EncodeRow($data);
  }

  /**
   * Creates new user for practice
   *
   * @param int $practiceId
   * @param array $data
   * @param Medoo|null $db
   * @param bool $first
   * @return bool
   */
  public function newUser($practiceId, array $data, Medoo $db = null, $first = false) {
    if ($db === null) {
      $db = databaseConnect();

      if ($db === false) {
        return false;
      }
    }

    if ($first === false) {
      $users = $this->loadUsers($practiceId);

      if ($users === false) {
        return false;
      }

      $data['code'] = count($users) + 1;
    } else {
      $data['code'] = 1;
    }

    if (!isset($data['title'])) {
      $data['title'] = "";
    }

    if (!isset($data['authorization'])) {
      $data['authorization'] = Auth::DefaultAuthorization();
    }

    $encodedData = $this->encodeData($data);

    $result = $db->insert('user', [
      'practice_id' => $practiceId,
      'position_id' => $encodedData['position_id'],
      'code' => $encodedData["code"],
      'password' => md5($encodedData['password']),
      'e_title' => $encodedData['e_title'],
      'e_name' => $encodedData['e_name'],
      'e_surname' => $encodedData['e_surname'],
      'gender' => $encodedData['gender'],
      'authorization' => $encodedData["authorization"],
      'reset_password' => (isset($encodedData["reset_password"]) ? $encodedData["reset_password"] : false)
    ]);

    return ($result !== false);
  }

  /**
   * Updates user data
   *
   * @param $practiceId
   * @param $userId
   * @param array $data
   * @return bool
   */
  public function updateUser($practiceId, $userId, array $data) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $encodedData = $this->encodeData($data);

    $cols = [
      'position_id',
      'password',
      'e_title',
      'e_name',
      'e_surname',
      'gender',
      'reset_password'
    ];
    $editData = [];

    foreach ($cols as $col) {
      if (isset($encodedData[$col])) {
        $editData[$col] = $encodedData[$col];
      }
    }

    if (count($editData) === 0) {
      return false;
    }

    $result = $db->update('user', $editData, [
      'id' => $userId,
      'practice_id' => $practiceId,
      'deleted' => null
    ]);

    return ($result !== false);
  }

  /**
   * Deletes single practice user
   *
   * @param $practiceId
   * @param $userId
   * @return bool
   */
  public function deleteUser($practiceId, $userId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->update('user', [
      'deleted' => (new Moment())->format()
    ], [
      'id' => $userId,
      'practice_id' => $practiceId
    ]);

    return ($result !== false);
  }

  /**
   * Gets single user name and surname
   *
   * @param $userId
   * @param Medoo|null $db
   * @return array|bool
   */
  public function getUserName($userId, Medoo $db = null) {
    if ($db === null) {
      $db = databaseConnect();

      if ($db === false) {
        return false;
      }
    }

    $result = $db->select('user', "*", [
      "id" => $userId,
      "LIMIT" => [
        0,
        1
      ]
    ]);

    if ($result === false || count($result) === 0) {
      return false;
    }

    $user = MysqlLock\MysqlLock::DecodeRow($result[0]);

    return [
      "title" => $user["title"],
      "name" => $user["name"],
      "surname" => $user["surname"]
    ];
  }

  public function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
      $index = rand(0, $count - 1);

      $result .= mb_substr($chars, $index, 1);
    }

    return $result;
  }
}
