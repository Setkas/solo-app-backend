<?php

require_once("./src/routes/user/user-controller.php");

class termController
{
    /**
     * Loads single client term
     * @param $clientId
     * @param $termId
     * @return bool|mixed
     */
    public function loadTerm($clientId, $termId) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $result = $db->select('term', "*", [
            "id" => $termId,
            "client_id" => $clientId,
            "LIMIT" => [
                0,
                1
            ],
            "ORDER" => [
                "date" => "DESC"
            ]
        ]);

        if ($result === false || count($result) === 0) {
            return false;
        }

        $term = $result[0];

        $uc = new userController();

        $term["user"] = $uc->getUserName($term["user_id"]);

        return $term;
    }

    /**
     * Loads all client's terms
     * @param $clientId
     * @return array|bool
     */
    public function loadTerms($clientId) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $terms = $db->select('term', "*", [
            "client_id" => $clientId,
            "ORDER" => [
                "date" => "DESC"
            ]
        ]);

        if ($terms === false || count($terms) === 0) {
            return false;
        }

        $uc = new userController();

        foreach ($terms as $key => $term) {
            $terms[$key]["user"] = $uc->getUserName($term["user_id"]);
        }

        return $terms;
    }

    /**
     * Inserts new term data
     * @param $userId
     * @param $clientId
     * @param array $data
     * @return bool
     */
    public function newTerm($userId, $clientId, array $data) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        if (!isset($data["note"])) {
            $data["note"] = "";
        }

        $result = $db->insert('term', [
            'client_id' => $clientId,
            'user_id' => $userId,
            'date' => $data["date"],
            'teeth_upper' => $data['teeth_upper'],
            'teeth_lower' => $data['teeth_lower'],
            'bleed_upper_inner' => $data['bleed_upper_outer'],
            'bleed_upper_outer' => $data['bleed_upper_outer'],
            'bleed_upper_middle' => $data['bleed_upper_middle'],
            'bleed_lower_inner' => $data['bleed_lower_outer'],
            'bleed_lower_outer' => $data['bleed_lower_outer'],
            'bleed_lower_middle' => $data['bleed_lower_middle'],
            'stix_upper' => $data["stix_upper"],
            'stix_lower' => $data["stix_lower"],
            'pass_upper' => $data["pass_upper"],
            'pass_lower' => $data["pass_lower"],
            'tartar' => $data["tartar"],
            'next_date' => $data["next_date"],
            'note' => $data["note"]
        ]);

        return ($result !== false);
    }

    /**
     * Updates single term data
     * @param $userId
     * @param $clientId
     * @param $termId
     * @param array $data
     * @return bool
     */
    public function updateTerm($userId, $clientId, $termId, array $data) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $cols = [
            'date',
            'teeth_upper',
            'teeth_lower',
            'bleed_upper_inner',
            'bleed_upper_outer',
            'bleed_upper_middle',
            'bleed_lower_inner',
            'bleed_lower_outer',
            'bleed_lower_middle',
            'stix_upper',
            'stix_lower',
            'pass_upper',
            'pass_lower',
            'tartar',
            'next_date',
            'note'
        ];
        $editData = [];

        foreach ($cols as $col) {
            if (isset($data[$col])) {
                $editData[$col] = $data[$col];
            }
        }

        if (count($editData) === 0) {
            return false;
        }

        $editData["user_id"] = $userId;

        $result = $db->update('term', $editData, [
            'id' => $termId,
            'client_id' => $clientId
        ]);

        return ($result !== false);
    }
}
