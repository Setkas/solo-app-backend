<?php

require_once("./src/common/variables.php");
require_once("./src/common/mysqlLock.php");
require_once("./src/common/authorization.php");
require_once("./src/common/mailer.php");

use Commons\Variables;
use Medoo\Medoo;

function databaseConnect() {
  try {
    return new Medoo([
      'database_type' => 'mysql',
      'database_name' => Variables\MysqlCredentials::$Database,
      'server' => Variables\MysqlCredentials::$Host,
      'username' => Variables\MysqlCredentials::$User,
      'password' => Variables\MysqlCredentials::$Password,
      'charset' => 'utf8',
      'prefix' => 'sa_',
    ]);
  } catch (Exception $exception) {
    return false;
  }
}
