<?php

use Medoo\Medoo;
use \Commons\Authorization\Auth;
use Commons\Variables;

class loginController
{
    /**
     * Login user and generate JWT token with expire time
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
            "user.id(id_user)"
        ], [
            "practice.code[~]" => $practice,
            "user.code" => $user,
            "user.password[~]" => md5($password),
            "practice.deleted" => 0,
            "user.deleted" => 0,
            "LIMIT" => [
                0,
                1
            ]
        ]);

        if (!$result || count($result) === 0) {
            return false;
        }

        return Auth::createToken($result[0]["id_practice"], $result[0]["id_user"]);
    }
}
