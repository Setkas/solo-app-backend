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
        try {
            $db = new Medoo([
                'database_type' => 'mysql',
                'database_name' => Variables\MysqlCredentials::$Database,
                'server' => Variables\MysqlCredentials::$Host,
                'username' => Variables\MysqlCredentials::$User,
                'password' => Variables\MysqlCredentials::$Password,
                'charset' => 'utf8']);
        } catch (Exception $exception) {
            return false;
        }

        $result = $db->select('user', "*", [
            "id" => $userId,
            "LIMIT" => [0, 1]
        ]);

        if($result === false || count($result) === 0) {
            return false;
        }

        return MysqlLock\MysqlLock::DecodeRow($result[0]);
    }
}
