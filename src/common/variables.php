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

    /**
     * Key for JWT token for password reset
     * @var string
     */
    public static $ResetJwt = "Z0Yqje9NincZEdGrKIkP";
}

/**
 * Coordinates for image creation
 * @package Commons\Variables
 */
class ImageCoordinates
{
    /**
     * Upper jaw stix color X
     * @var array
     */
    public static $stixUpperX = [
        77,
        85,
        111,
        140,
        178,
        227,
        296,
        386,
        469,
        540,
        595,
        630,
        657,
        684,
        690
    ];

    /**
     * Lower jaw stix color X
     * @var array
     */
    public static $stixLowerX = [
        108,
        137,
        168,
        215,
        246,
        292,
        339,
        388,
        436,
        485,
        525,
        560,
        605,
        639,
        670
    ];

    /**
     * Upper jaw stix color Y
     * @var array
     */
    public static $stixUpperY = [
        533,
        456,
        361,
        307,
        252,
        198,
        167,
        152,
        167,
        197,
        252,
        306,
        361,
        456,
        533
    ];

    /**
     * Lower jaw stix color Y
     * @var array
     */
    public static $stixLowerY = [
        765,
        835,
        923,
        983,
        1025,
        1065,
        1080,
        1090,
        1085,
        1065,
        1025,
        983,
        923,
        835,
        765
    ];

    /**
     * Upper jaw teeth X
     * @var array
     */
    public static $teethUpperX = [
        114,
        116,
        124,
        157,
        190,
        228,
        275,
        332,
        398,
        473,
        510,
        540,
        570,
        590,
        605,
        610
    ];

    /**
     * Lower jaw teeth X
     * @var array
     */
    public static $teethLowerX = [
        143,
        157,
        172,
        215,
        250,
        292,
        332,
        373,
        411,
        443,
        469,
        500,
        530,
        545,
        575,
        598
    ];

    /**
     * Upper jaw teeth Y
     * @var array
     */
    public static $teethUpperY = [
        563,
        490,
        398,
        348,
        298,
        245,
        210,
        196,
        196,
        210,
        245,
        298,
        348,
        398,
        490,
        563
    ];

    /**
     * Lower jaw teeth Y
     * @var array
     */
    public static $teethLowerY = [
        695,
        760,
        825,
        903,
        951,
        988,
        1006,
        1020,
        1020,
        1007,
        967,
        957,
        903,
        825,
        760,
        695
    ];
}
