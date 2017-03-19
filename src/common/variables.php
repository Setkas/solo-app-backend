<?php

namespace Commons\Variables;

/**
 * MYSQL database settings
 * @package Commons\Variables
 */
class MysqlCredentials
{
    /**
     * Host name and port
     * @var string
     */
    public static $Host = "localhost";

    /**
     * Username for access
     * @var string
     */
    public static $User = "root";

    /**
     * Password for access user
     * @var string|null
     */
    public static $Password = null;

    /**
     * Default database to use
     * @var string
     */
    public static $Database = "soloapp";
}

/**
 * Keys for key creation and database data
 * @package Commons\Variables
 */
class LockKeys
{
    /**
     * Key for JWT token
     * @var string
     */
    public static $Jwt = "PuC2FdHUtWGpxglVfe73";

    /**
     * Key for MYSQL encrypted data
     * @var string
     */
    public static $Mysql = "m8w545MWTsEFuZy1Ns4d";
}
