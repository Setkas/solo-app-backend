<?php

use Commons\MysqlLock;

class clientController {
  /**
   * Encoded columns in database
   *
   * @var array
   */
  private $eCols = [
    'name',
    'surname',
    'address',
    'phone'
  ];

  /**
   * Gets client data from database
   *
   * @param $practiceId
   * @param $clientId
   * @return bool|mixed
   */
  public function loadClient($practiceId, $clientId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('client', "*", [
      "id" => $clientId,
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

    $client = MysqlLock\MysqlLock::DecodeRow($result[0]);

    $client["password"] = ($client["password"] !== null);

    return $client;
  }

  /**
   * Searches for all users matching the query
   *
   * @param $practiceId
   * @param $query
   * @return array|bool
   */
  public function findClients($practiceId, $query) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $exp = array_filter(preg_split('/[,\s]+/', $query));

    if (isset($exp[1])) {
      $params = [
        "#" . strtoupper(substr($exp[1], 0, 1)) . "#" . strtoupper(substr($exp[0], 0, 1)),
        "#" . strtoupper(substr($exp[0], 0, 1)) . "#" . strtoupper(substr($exp[1], 0, 1))
      ];
    } else {
      $params = "%#" . strtoupper(substr($exp[0], 0, 1)) . "%";
    }

    $result = $db->select('client', "*", [
      "practice_id" => $practiceId,
      "deleted" => null,
      "keywords[~]" => $params
    ]);

    if ($result === false || count($result) === 0) {
      return false;
    }

    $clients = MysqlLock\MysqlLock::DecodeBatch($result);
    $mClients = [];

    foreach ($clients as $c) {
      if (isset($exp[1])) {
        if ((strpos(strtolower($c["name"]), strtolower($exp[1])) !== false
             && strpos(strtolower($c["surname"]), strtolower($exp[0])) !== false)
            || (strpos(strtolower($c["name"]), strtolower($exp[0])) !== false
                && strpos(strtolower($c["surname"]), strtolower($exp[1])) !== false)) {
          $mClients[] = $c;
        }
      } else {
        if (strpos(strtolower($c["name"]), strtolower($exp[0])) !== false
            || strpos(strtolower($c["surname"]), strtolower($exp[0])) !== false) {
          $mClients[] = $c;
        }
      }
    }

    return $mClients;
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
   * Creates new client
   *
   * @param $practiceId
   * @param array $data
   * @return bool
   */
  public function newClient($practiceId, array $data) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $keywords = "#" . strtoupper(substr($data["name"], 0, 1)) . "#" . strtoupper(substr($data["surname"], 0, 1));

    if (!isset($data["phone"])) {
      $data["phone"] = "";
    }

    if (!isset($data["email"])) {
      $data["email"] = "";
    }

    $encodedData = $this->encodeData($data);

    $result = $db->insert('client', [
      'practice_id' => $practiceId,
      'keywords' => $keywords,
      'e_name' => $encodedData["e_name"],
      'e_surname' => $encodedData['e_surname'],
      'e_address' => $encodedData['e_address'],
      'e_phone' => $encodedData['e_surname'],
      'birth_date' => $encodedData['birth_date'],
      'email' => $encodedData["email"],
      'gender' => $encodedData["gender"]
    ]);

    return ($result === false) ? false : (int) $db->id();
  }

  /**
   * Updates client data
   *
   * @param $practiceId
   * @param $clientId
   * @param array $data
   * @return bool
   */
  public function updateClient($practiceId, $clientId, array $data) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $encodedData = $this->encodeData($data);

    $cols = [
      'e_name',
      'e_surname',
      'e_address',
      'e_phone',
      'birth_date',
      'email',
      'gender',
      'password',
      'changes_reminder'
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

    if (isset($editData["e_name"]) && isset($editData["e_surname"])) {
      $editData["keywords"] = "#" . strtoupper(substr($data["name"], 0, 1)) . "#"
                              . strtoupper(substr($data["surname"], 0, 1));
    }

    $result = $db->update('client', $editData, [
      'id' => $clientId,
      'practice_id' => $practiceId,
      'deleted' => null
    ]);

    return ($result !== false);
  }

  /**
   * Deletes single client
   *
   * @param $practiceId
   * @param $userId
   * @return bool
   */
  public function deleteClient($practiceId, $userId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->update('client', [
      'deleted' => 1
    ], [
      'id' => $userId,
      'practice_id' => $practiceId
    ]);

    return ($result !== false);
  }

  /**
   * gets client basic information
   *
   * @param $clientId
   * @return array|bool
   */
  public function getClientName($clientId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('client', "*", [
      "id" => $clientId,
      "LIMIT" => [
        0,
        1
      ]
    ]);

    if ($result === false || count($result) === 0) {
      return false;
    }

    $client = MysqlLock\MysqlLock::DecodeRow($result[0]);

    return [
      "name" => $client["name"],
      "surname" => $client["surname"],
      "birth_date" => $client["birth_date"],
      "email" => $client["email"]
    ];
  }
}
