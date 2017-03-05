<?php
namespace Commons\Authorization;

use Exception;
use Medoo\Medoo;
use Moment\Moment;
use Firebase\JWT\JWT;
use Commons\Variables;

class Auth {
    /**
     * Creates authorization token for user
     * @param $practice
     * @param $user
     * @return array
     */
    public static function createToken($practice, $user) {
        $moment = new Moment();
        $expireTime = $moment->addDays(1)->format();

        $token = [
            "practice" => $practice,
            "user" => $user,
            "expire" => $expireTime
        ];

        $jwt = JWT::encode($token, Variables\LockKeys::$Jwt, 'HS256');

        return [
            "token" => $jwt,
            "expire" => $expireTime
        ];
    }

    /**
     * Checks if token is valid
     * @param $token
     * @return bool
     */
    public static function checkToken($token) {
        if (strpos($token, 'Bearer') !== false) {
            $token = str_replace('Bearer ', '', $token);
        }

        $decoded = JWT::decode($token, Variables\LockKeys::$Jwt, ['HS256']);

        $tArray = json_decode(json_encode($decoded), true);

        $expire = new Moment($tArray['expire']);
        $diff = $expire->fromNow()->getDirection();

        if($diff === 'past') {
            return false;
        }

        try {
            $db = new Medoo([
                'database_type' => 'mysql',
                'database_name' => Variables\MysqlCredentials::$Database,
                'server' => Variables\MysqlCredentials::$Host,
                'username' => Variables\MysqlCredentials::$User,
                'password' => Variables\MysqlCredentials::$Password, 'charset' => 'utf8']);
        } catch (Exception $exception) {
            return false;
        }

        $result = $db->select('practice', [
            "[>]user" => [
                "id" => "id"
            ]
        ], [
            "practice.id(id_practice)",
            "user.id(id_user)"
        ], [
            "practice.id" => $tArray['practice'],
            "user.id" => $tArray['user'],
            "LIMIT" => [0, 1]
        ]);

        if(!$result || count($result) === 0) {
            return false;
        }

        return true;
    }
}
