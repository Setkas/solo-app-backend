<?php

use Medoo\Medoo;

class setupController {
  /**
   * Gets client data from database
   *
   * @param $practiceId
   * @param Medoo|null $db
   * @return bool|mixed
   */
  public function loadSetup($practiceId, Medoo $db = null) {
    if ($db === null) {
      $db = databaseConnect();

      if ($db === false) {
        return false;
      }
    }

    $result = $db->select('setup', "*", [
      "practice_id" => $practiceId
    ]);

    if ($result === false || count($result) === 0) {
      return false;
    }

    $array = [];

    foreach ($result as $item) {
      $array[$item["name"]] = $item["value"];
    }

    return $array;
  }

  /**
   * Saves setup values for user
   *
   * @param $practiceId
   * @param array $data
   * @return bool
   */
  public function saveSetup($practiceId, array $data) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $setup = $this->loadSetup($practiceId, $db);
    $update = [];
    $insert = [];

    foreach ($data as $k => $v) {
      if (isset($setup[$k])) {
        $update[$k] = $v;
      } else {
        $insert[] = [
          "practice_id" => $practiceId,
          "name" => $k,
          "value" => $v
        ];
      }
    }

    $return = true;

    if (count($insert) > 0) {
      $result = $db->insert('setup', $insert);

      if ($result === false) {
        $return = false;
      }
    }

    if (count($update) > 0) {
      foreach ($update as $k => $v) {
        $result = $db->update('setup', [
          "value" => $v
        ], [
          "practice_id" => $practiceId,
          "name" => $k
        ]);

        if ($result === false) {
          $return = false;
        }
      }
    }

    return $return;
  }
}
