<?php

require_once("./src/routes/user/user-controller.php");

use Commons\Variables\ImageCoordinates;
use Intervention\Image\ImageManager;
use Moment\Moment;

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

    private function countBob($term) {
        $bob = [
            "MAX" => 0,
            "CURRENT" => 0
        ];

        for ($i = 0; $i < 15; $i++) {
            if (!(substr($term["teeth_upper"], $i, 1) === '0' || (substr($term["teeth_upper"], $i,
                        1) === 'L' && substr($term["teeth_upper"], $i + 1, 1) === '0'))
            ) {
                $bob["MAX"]++;

                if (substr($term["bleed_upper_middle"], $i, 1) === "1") {
                    $bob["CURRENT"]++;
                }
            }

            if (!(substr($term["teeth_lower"], $i, 1) === '0' || (substr($term["teeth_lower"], $i,
                        1) === 'L' && substr($term["teeth_lower"], $i + 1, 1) === '0'))
            ) {
                $bob["MAX"]++;

                if (substr($term["bleed_lower_middle"], $i, 1) === "1") {
                    $bob["CURRENT"]++;
                }
            }
        }

        return $bob;
    }

    public function generateImage($clientId, $termId, $client) {
        $term = $this->loadTerm($clientId, $termId);

        if ($term === false) {
            return false;
        }

        $manager = new ImageManager(array('driver' => 'gd'));

        $image = $manager->make('./src/assets/pass_background.png');

        $logo = $manager->make('./src/assets/logo.png');

        $logo = $logo->resize($image->width() / 3, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $image = $image->insert($logo, 'center');

        $teethPath = "./src/assets/teeth/";
        $stixPath = "./src/assets/stix/";

        for ($i = 0; $i < 16; $i++) {
            if (substr($term["teeth_upper"], $i, 1) !== '0' && substr($term["teeth_upper"], $i, 1) !== 'L') {
                $fileName = $teethPath . "uj-" . $i . ".png";

                if (file_exists($fileName)) {
                    $image = $image->insert($fileName, 'top-left', ImageCoordinates::$teethUpperX[$i],
                        ImageCoordinates::$teethUpperY[$i]);
                }
            }

            if (substr($term["teeth_lower"], $i, 1) !== '0' && substr($term["teeth_lower"], $i, 1) !== 'L') {
                $fileName = $teethPath . "lj-" . $i . ".png";

                if (file_exists($fileName)) {
                    $image = $image->insert($fileName, 'top-left', ImageCoordinates::$teethLowerX[$i],
                        ImageCoordinates::$teethLowerY[$i]);
                }
            }
        }

        for ($i = 0; $i < 15; $i++) {
            if (!(substr($term["teeth_upper"], $i, 1) === '0' || (substr($term["teeth_upper"], $i,
                        1) === 'L' && substr($term["teeth_upper"], $i + 1, 1) === '0'))
            ) {
                $fileName = $stixPath . "stix-" . substr($term["stix_upper"], $i, 1) . ".png";

                if (file_exists($fileName)) {
                    $image = $image->insert($fileName, 'top-left', ImageCoordinates::$stixUpperX[$i],
                        ImageCoordinates::$stixUpperY[$i]);
                }
            }

            if (!(substr($term["teeth_lower"], $i, 1) === '0' || (substr($term["teeth_lower"], $i,
                        1) === 'L' && substr($term["teeth_upper"], $i + 1, 1) === '0'))
            ) {
                $fileName = $stixPath . "stix-" . substr($term["stix_lower"], $i, 1) . ".png";

                if (file_exists($fileName)) {
                    $image = $image->insert($fileName, 'top-left', ImageCoordinates::$stixLowerX[$i],
                        ImageCoordinates::$stixLowerY[$i]);
                }
            }
        }

        $setFont25 = function ($font) {
            $font->file("./src/assets/fonts/arial.ttf")
                ->color('#333333')
                ->align('center');
            $font->size(25);
        };

        $setFont35 = function ($font) {
            $font->file("./src/assets/fonts/arial.ttf")
                ->color('#333333')
                ->align('center');
            $font->size(35);
        };

        $setFont50 = function ($font) {
            $font->file("./src/assets/fonts/arial.ttf")
                ->color('#333333')
                ->align('center');
            $font->size(50);
        };

        $moment = new Moment($term["date"]);
        $moment1 = new moment($client["birth_date"]);

        $bob = $this->countBob($term);

        $image = $image->text($client["name"] . " " . $client["surname"], $image->width() / 2, 50, $setFont50);
        $image = $image->text($moment1->format("d. m. Y"), $image->width() / 2, 100, $setFont35);

        $image = $image->text('LEFT', $image->width() / 5, 675, $setFont25);
        $image = $image->text('RIGHT', $image->width() / 1.25, 675, $setFont25);
        $image = $image->text('UPPER', $image->width() / 2, 330, $setFont25);
        $image = $image->text('LOWER', $image->width() / 2, 970, $setFont25);

        $image = $image->text($moment->format("d. m. Y"), $image->width() / 2, 1160, $setFont35);
        $image = $image->text("BOB: " . $bob["CURRENT"] . "/" . $bob["MAX"], $image->width() / 2, 1220, $setFont50);

        return $image->response('png');
    }
}
