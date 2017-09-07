<?php
require_once("./src/routes/user/user-controller.php");

use Medoo\Medoo;
use Moment\Moment;
use Commons\MysqlLock;

class practiceController
{
    /**
     * Encoded columns in database
     * @var array
     */
    private $eCols = [
        'address',
        'phone',
        'contact_email',
        'system_email',
        'webpages'
    ];

    /**
     * Duration in days for trial period
     * @var int
     */
    private $trialLength = 30;

    /**
     * Gets practice data from database
     * @param $practiceId
     * @return bool|mixed
     */
    public function loadPractice($practiceId) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $result = $db->select('practice', "*", [
            "id" => $practiceId,
            "deleted" => 0,
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
     * Generates login code for practice
     * @param Medoo $db
     * @param $company
     * @return bool|string
     */
    private function generateCode(Medoo &$db, $company) {
        $alphanumeric = preg_replace("/[^a-zA-Z0-9s]/", "", $company);
        $codeName = strtoupper(substr($alphanumeric, 0, 3));

        if (strlen($codeName) === 0) {
            return false;
        }

        $code = false;
        $try = 0;

        while ($code === false) {
            $testCode = $codeName . rand(1000, 9999);

            $result = $db->select('practice', "*", [
                "code[~]" => $testCode,
                "LIMIT" => [
                    0,
                    1
                ]
            ]);

            if ($result !== false && count($result) === 0) {
                $code = $testCode;
            }

            if (++$try == 5) {
                break;
            }
        }

        return $code;
    }

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
     * Checks if practice exists in database
     * @param Medoo $db
     * @param $company
     * @return bool
     */
    private function practiceExists(Medoo &$db, $company) {
        $result = $db->select('practice', '*', [
            'company[~]' => $company,
            'LIMIT' => [
                0,
                1
            ]
        ]);

        return ($result === false || count($result) > 0);
    }

    /**
     * Create new practice and user
     * @param $data
     * @return bool
     */
    public function newPractice($data) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        if (!isset($data['webpages'])) {
            $data['webpages'] = "";
        }

        if (!isset($data['title'])) {
            $data['title'] = "";
        }

        $encodedData = $this->encodeData($data);

        if ($this->practiceExists($db, $encodedData['company'])) {
            return false;
        }

        $moment = new Moment();
        $valid = $moment->addDays($this->trialLength)
            ->format();

        $code = $this->generateCode($db, $encodedData['company']);

        if ($code === false) {
            return false;
        }

        $result = $db->insert('practice', [
            'language_id' => $encodedData['language_id'],
            'code' => $code,
            'company' => $encodedData['company'],
            'e_address' => $encodedData['e_address'],
            'valid' => $valid,
            'e_phone' => $encodedData['e_phone'],
            'e_contact_email' => $encodedData['e_contact_email'],
            'e_webpages' => $encodedData['e_webpages'],
        ]);

        if ($result === false) {
            return false;
        }

        $practiceId = $db->id();

        $uc = new userController();

        $result = $uc->newUser($practiceId, [
            'position_id' => $data['position_id'],
            'password' => $data['password'],
            'title' => $data['title'],
            'name' => $data['name'],
            'surname' => $data['surname'],
            'gender' => $data['gender']
        ], $db, true);

        if ($result === false) {
            $db->delete('practice', [
                'id' => $practiceId
            ]);

            return false;
        }

        return true;
    }

    /**
     * Edits practice data
     * @param $id
     * @param $data
     * @return bool
     */
    public function editPractice($id, $data) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $encodedData = $this->encodeData($data);

        $cols = [
            'company',
            'e_address',
            'e_phone',
            'e_contact_email',
            'e_webpages',
            'e_system_email',
            'valid',
            'valid_reminder',
            'monthly_reminder',
            'changes_reminder'
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

        if (isset($editData['company']) && $this->practiceExists($db, $editData['company'])) {
            return false;
        }

        $result = $db->update('practice', $editData, [
            'id' => $id,
            'deleted' => 0
        ]);

        return ($result !== false);
    }

    /**
     * Deletes single practice
     * @param $practiceId
     * @param $userId
     * @return bool
     */
    public function deletePractice($practiceId, $userId) {
        $db = databaseConnect();

        if ($db === false) {
            return false;
        }

        $uc = new userController();

        if (!$uc->isMasterUser($userId)) {
            return false;
        }

        $result = $db->update('practice', [
            'deleted' => 1
        ], [
            'id' => $practiceId
        ]);

        return ($result !== false);
    }
}
