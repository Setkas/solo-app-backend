<?php

use Medoo\Medoo;
use Commons\MysqlLock;
use Commons\Variables;

class positionController
{
    /**
     * Gets single position by ID
     * @param $positionId
     * @return bool|mixed
     */
    public function loadPosition($positionId) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $result = $db->select('position', "*", [
            "id" => $positionId,
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
     * Gets single position by ID
     * @return bool|mixed
     */
    public function loadPositions() {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $result = $db->select('position', "*", [
            "ORDER" => "rating"
        ]);

        if ($result === false || count($result) === 0) {
            return false;
        }

        return MysqlLock\MysqlLock::DecodeBatch($result);
    }
}
