<?php

use Commons\MysqlLock;

class languageController
{
    /**
     * Gets single language by ID
     * @param $languageId
     * @return bool|mixed
     */
    public function loadLanguage($languageId) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $result = $db->select('language', "*", [
            "id" => $languageId,
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

    /**
     * Gets single language by ID
     * @return bool|mixed
     */
    public function loadLanguages() {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $result = $db->select('language', "*", [
            "ORDER" => "rating"
        ]);

        if ($result === false || count($result) === 0) {
            return false;
        }

        return MysqlLock\MysqlLock::DecodeBatch($result);
    }
}
