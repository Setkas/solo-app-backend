<?php

use Medoo\Medoo;
use Commons\MysqlLock;
use Commons\Variables;

class userController
{
    /**
     * Gets user data from database
     * @param $userId
     * @return bool|mixed
     */
    public function loadUser($userId) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
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

        return MysqlLock\MysqlLock::DecodeRow($result[0]);
    }
}
