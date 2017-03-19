<?php

use Commons\MysqlLock;
use Medoo\Medoo;

class importController
{
    /**
     * Encoded columns in database
     * @var array
     */
    private $eCols = [
        'name',
        'surname',
        'address',
        'phone'
    ];

    /**
     * Encodes data for database use
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
     * Imports client data
     * @param $practiceId
     * @param $data
     * @return bool
     */
    public function importClient($practiceId, $data) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $result = $db->select('client', "*", [
            "practice_id" => $practiceId,
            "import_id" => $data["import_id"],
            "LIMIT" => [
                0,
                1
            ]
        ]);

        if (count($result) === 0) {
            $clientId = $this->newClient($db, $practiceId, $data);

            if ($clientId === false) {
                return false;
            }

            return $this->addTerm($db, $clientId, $data);
        } else {
            $clientId = $result[0]["id"];

            $result = $this->updateClient($db, $practiceId, $clientId, $data);

            if ($result === false) {
                return false;
            }

            return $this->addTerm($db, $clientId, $data);
        }
    }

    /**
     * Creates new client
     * @param Medoo $db
     * @param $practiceId
     * @param array $data
     * @return bool
     */
    private function newClient(Medoo $db, $practiceId, array $data) {
        $keywords = "#" . strtoupper(substr($data["name"], 0, 1)) . "#" . strtoupper(substr($data["surname"], 0, 1));

        $data["birth_date"] = (!isset($data["birth_date"])) ? "1970-01-01T00:00:00Z" : $data["birth_date"];
        $data["gender"] = (!isset($data["gender"])) ? 0 : $data["gender"];
        $data["phone"] = (!isset($data["address"])) ? "" : $data["address"];
        $data["email"] = (!isset($data["email"])) ? "" : $data["email"];
        $data["address"] = (!isset($data["address"])) ? "" : $data["address"];

        $encodedData = $this->encodeData($data);

        $result = $db->insert('client', [
            'practice_id' => $practiceId,
            'import_id' => $encodedData["import_id"],
            'keywords' => $keywords,
            'e_name' => $encodedData["e_name"],
            'e_surname' => $encodedData['e_surname'],
            'e_address' => $encodedData['e_address'],
            'e_phone' => $encodedData['e_surname'],
            'birth_date' => $encodedData['gender'],
            'email' => $encodedData["email"],
            'gender' => $encodedData["gender"]
        ]);

        return ($result !== false) ? $db->id() : false;
    }

    /**
     * Updates client data
     * @param Medoo $db
     * @param $practiceId
     * @param $clientId
     * @param array $data
     * @return bool
     */
    public function updateClient(Medoo $db, $practiceId, $clientId, array $data) {
        $encodedData = $this->encodeData($data);

        $cols = [
            'e_name',
            'e_surname',
            'e_address',
            'e_phone',
            'birth_date',
            'email',
            'gender'
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
            $editData["keywords"] = strtoupper(substr($data["name"], 0, 1)) . "#" . strtoupper(substr($data["surname"],
                    0, 1));
        }

        $result = $db->update('client', $editData, [
            'id' => $clientId,
            'practice_id' => $practiceId,
            'deleted' => 0
        ]);

        return ($result !== false);
    }

    /**
     * Adds new term from import
     * @param Medoo $db
     * @param $clientId
     * @param $data
     * @return bool
     */
    public function addTerm(Medoo $db, $clientId, $data) {
        $data["date"] = (!isset($data["date"])) ? "1970-01-01T00:00:00Z" : $data["date"];
        $data["next_date"] = (!isset($data["next_date"])) ? "1970-01-01T00:00:00Z" : $data["next_date"];
        $data["teeth_upper"] = (!isset($data["teeth_upper"])) ? "1111111111111111" : $data["teeth_upper"];
        $data["teeth_lower"] = (!isset($data["teeth_lower"])) ? "1111111111111111" : $data["teeth_lower"];
        $data["bleed_upper_inner"] = (!isset($data["bleed_upper_inner"])) ? "0000000000000000" : $data["bleed_upper_inner"];
        $data["bleed_upper_outer"] = (!isset($data["bleed_upper_outer"])) ? "0000000000000000" : $data["bleed_upper_outer"];
        $data["bleed_upper_middle"] = (!isset($data["bleed_upper_middle"])) ? "000000000000000" : $data["bleed_upper_middle"];
        $data["bleed_lower_inner"] = (!isset($data["bleed_lower_inner"])) ? "0000000000000000" : $data["bleed_lower_inner"];
        $data["bleed_lower_outer"] = (!isset($data["bleed_lower_outer"])) ? "0000000000000000" : $data["bleed_lower_outer"];
        $data["bleed_lower_middle"] = (!isset($data["bleed_lower_middle"])) ? "000000000000000" : $data["bleed_lower_middle"];
        $data["stix_upper"] = (!isset($data["stix_upper"])) ? "000000000000000" : $data["stix_upper"];
        $data["stix_lower"] = (!isset($data["stix_lower"])) ? "000000000000000" : $data["stix_lower"];
        $data["pass_upper"] = (!isset($data["pass_upper"])) ? "000000000000000" : $data["pass_upper"];
        $data["pass_lower"] = (!isset($data["pass_lower"])) ? "000000000000000" : $data["pass_lower"];
        $data["tartar"] = (!isset($data["tartar"])) ? "00" : $data["tartar"];

        $result = $db->insert('term', [
            "client_id" => $clientId,
            "date" => $data["date"],
            "next_date" => $data["next_date"],
            "teeth_upper" => $data["teeth_upper"],
            "teeth_lower" => $data["teeth_lower"],
            "bleed_upper_inner" => $data["bleed_upper_inner"],
            "bleed_upper_outer" => $data["bleed_upper_outer"],
            "bleed_upper_middle" => $data["bleed_upper_middle"],
            "bleed_lower_inner" => $data["bleed_lower_inner"],
            "bleed_lower_outer" => $data["bleed_lower_outer"],
            "bleed_lower_middle" => $data["bleed_lower_middle"],
            "stix_upper" => $data["stix_upper"],
            "stix_lower" => $data["stix_lower"],
            "pass_upper" => $data["pass_upper"],
            "pass_lower" => $data["pass_lower"],
            "tartar" => $data["tartar"]
        ]);

        return ($result !== false);
    }
}
