<?php

use Commons\Variables\LockKeys;
use Firebase\JWT\JWT;
use Moment\Moment;
use Commons\Authorization\Auth;

class loginController {
  /**
   * Login user and generate JWT token with expire time
   *
   * @param $practice
   * @param $user
   * @param $password
   * @return array|bool
   */
  public function login($practice, $user, $password) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('practice', [
      "[>]user" => [
        "id" => "practice_id"
      ]
    ], [
      "practice.id(id_practice)",
      "user.id(id_user)",
      "valid"
    ], [
      "practice.code[~]" => $practice,
      "user.code" => $user,
      "user.password" => md5($password),
      "practice.deleted" => null,
      "user.deleted" => null,
      "LIMIT" => [
        0,
        1
      ]
    ]);

    if (!$result || count($result) === 0) {
      return false;
    }

    $valid = new Moment($result[0]["valid"]);

    if ($valid->fromNow()
          ->getDirection() === 'past') {
      return null;
    }

    return Auth::createToken($result[0]["id_practice"], $result[0]["id_user"]);
  }

  /**
   * Finds practice adn user by their access codes
   *
   * @param $practice
   * @param $user
   * @return array|bool|null
   */
  public function findPracticeUser($practice, $user) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('practice', [
      "[>]user" => [
        "id" => "practice_id"
      ]
    ], [
      "practice.id(id_practice)",
      "user.id(id_user)",
      "valid"
    ], [
      "practice.code[~]" => $practice,
      "user.code" => $user,
      "practice.deleted" => null,
      "user.deleted" => null,
      "user.reset_password" => 0,
      "LIMIT" => [
        0,
        1
      ]
    ]);

    if (!$result || count($result) === 0) {
      return false;
    }

    $valid = new Moment($result[0]["valid"]);

    if ($valid->fromNow()
          ->getDirection() === 'past') {
      return null;
    }

    return $result[0];
  }

  /**
   * Resets password for user and generates token
   *
   * @param $practiceId
   * @param $userId
   * @return array|bool
   */
  public function resetPassword($practiceId, $userId) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->update('user', [
      'reset_password' => 1
    ], [
      'id' => $userId,
      'practice_id' => $practiceId
    ]);

    if ($result === false) {
      return false;
    }

    $moment = new Moment();
    $expireTime = $moment->addDays(1)
      ->format();

    $jwt = JWT::encode([
      "practice" => $practiceId,
      "user" => $userId,
      "expire" => $expireTime
    ], LockKeys::$ResetJwt, 'HS256');

    return [
      "jwt" => $jwt,
      "expire" => $expireTime
    ];
  }

  /**
   * Updates user password
   *
   * @param $token
   * @param $password
   * @return bool
   */
  public function updatePassword($token, $password) {
    try {
      $decoded = JWT::decode($token, LockKeys::$ResetJwt, ['HS256']);
    } catch (Exception $e) {
      return false;
    }

    $tArray = json_decode(json_encode($decoded), true);

    $expire = new Moment($tArray['expire']);
    $diff = $expire->fromNow()
      ->getDirection();

    if ($diff === 'past') {
      return false;
    }

    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $result = $db->select('practice', [
      "[>]user" => [
        "id" => "practice_id"
      ]
    ], [
      "practice.id(id_practice)",
      "user.id(id_user)"
    ], [
      "practice.id" => $tArray['practice'],
      "user.id" => $tArray['user'],
      "practice.deleted" => null,
      "user.deleted" => null,
      "user.reset_password" => 1,
      "LIMIT" => [
        0,
        1
      ]
    ]);

    if (!$result || count($result) === 0) {
      return false;
    }

    $result = $db->update('user', [
      "password" => md5($password),
      "reset_password" => 0
    ], [
      "id" => $tArray['user'],
      "practice_id" => $tArray['practice']
    ]);

    return ($result !== false);
  }
}
