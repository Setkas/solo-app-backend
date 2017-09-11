<?php

namespace Commons\MysqlLock;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use Commons\Variables;

class MysqlLock {
  /**
   * Encodes row to save in database
   *
   * @param $row
   * @return mixed
   */
  public static function EncodeRow($row) {
    foreach ($row as $key => $column) {
      if (substr($key, 0, 2) === 'e_') {
        if (strlen($column) > 0) {
          $row[$key] = CryptoJSAES::encrypt($column, Variables\LockKeys::$Mysql);
        } else {
          $row[$key] = $column;
        }
      }
    }

    return $row;
  }

  /**
   * Decodes row to save for database
   *
   * @param $row
   * @return mixed
   */
  public static function DecodeRow($row) {
    foreach ($row as $key => $column) {
      if (substr($key, 0, 2) === 'e_') {
        if (strlen($column) > 0) {
          $row[substr($key, 2)] = CryptoJSAES::decrypt($column, Variables\LockKeys::$Mysql);
        } else {
          $row[substr($key, 2)] = $column;
        }

        unset($row[$key]);
      }
    }

    return $row;
  }

  /**
   * Encodes group of rows for database
   *
   * @param $batch
   * @return array
   */
  public static function EncodeBatch($batch) {
    if (!is_array($batch)) {
      return $batch;
    }

    foreach ($batch as $key => $row) {
      $batch[$key] = self::EncodeRow($row);
    }

    return $batch;
  }

  /**
   * Decodes group of rows from database
   *
   * @param $batch
   * @return array
   */
  public static function DecodeBatch($batch) {
    if (!is_array($batch)) {
      return $batch;
    }

    foreach ($batch as $key => $row) {
      $batch[$key] = self::DecodeRow($row);
    }

    return $batch;
  }
}
