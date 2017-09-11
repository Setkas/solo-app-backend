<?php

use Medoo\Medoo;

class setupController {
  /**
   * Gets client data from database
   *
   * @param $userId
   * @param Medoo|null $db
   * @return bool|mixed
   */
  public function loadSetup($userId, Medoo $db = null) {
    if ($db === null) {
      $db = databaseConnect();

      if ($db === false) {
        return false;
      }
    }

    $result = $db->select('setup', "*", [
      "user_id" => $userId
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
   * @param $userId
   * @param array $data
   * @return bool
   */
  public function saveSetup($userId, array $data) {
    $db = databaseConnect();

    if ($db === false) {
      return false;
    }

    $setup = $this->loadSetup($userId, $db);
    $update = [];
    $insert = [];

    foreach ($data as $k => $v) {
      if (isset($setup[$k])) {
        $update[$k] = $v;
      } else {
        $insert[] = [
          "user_id" => $userId,
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
          "user_id" => $userId,
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
