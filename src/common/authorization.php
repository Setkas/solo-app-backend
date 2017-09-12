<?php

namespace Commons\Authorization;

use Exception;
use Moment\Moment;
use Firebase\JWT\JWT;
use Commons\Variables;

class Auth {
  /**
   * Authorization levels list
   *
   * @var array
   */
  private static $authLevels = [
    "GUEST",
    "CLIENT",
    "USER",
    "MODERATOR",
    "ADMIN"
  ];

  /**
   * Creates authorization token for user
   *
   * @param $practice
   * @param $user
   * @return array
   */
  public static function createToken($practice, $user, $authorization) {
    $moment = new Moment();
    $expireTime = $moment->addDays(1)
      ->format();

    $token = [
      "practice" => $practice,
      "user" => $user,
      "expire" => $expireTime,
      "authorization" => $authorization
    ];

    $jwt = JWT::encode($token, Variables\LockKeys::$Jwt, 'HS256');

    return [
      "token" => $jwt,
      "expire" => $expireTime,
      "authorization" => $authorization
    ];
  }

  /**
   * Checks if token is valid
   *
   * @param $token
   * @return array|bool
   */
  public static function checkToken($token) {
    if (strpos($token, 'Bearer') !== false) {
      $token = str_replace('Bearer ', '', $token);
    }

    try {
      $decoded = JWT::decode($token, Variables\LockKeys::$Jwt, ['HS256']);
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
      "practice.id" => (int) $tArray['practice'],
      "user.id" => (int) $tArray['user'],
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

    return [
      'user' => (int) $tArray['user'],
      'practice' => (int) $tArray['practice']
    ];
  }

  /**
   * Gets default authorization for users
   *
   * @return mixed
   */
  public static function DefaultAuthorization() {
    return self::$authLevels[2];
  }

  /**
   * Gets default main practice user authorization
   *
   * @return mixed
   */
  public static function PracticeUserAuthorization() {
    return self::$authLevels[3];
  }
}
