<?php

namespace Flagship\Enum;

class FlagshipContext
{
    /**
     * Current device locale
     * @var string
     */
    const DEVICE_LOCALE = 'sdk_deviceLanguage';
    /**
     * Current device type  tablet, pc, server, iot, other
     * @var string
     */
    const DEVICE_TYPE = 'sdk_deviceType';
    /**
     * Current device model
     * @var string
     */
    const DEVICE_MODEL = 'sdk_deviceModel';
    /**
     * Current visitor city
     * @var string
     */
    const LOCATION_CITY = 'sdk_city';
    /**
     * Current visitor region
     * @var string
     */
    const LOCATION_REGION = 'sdk_region';

    /**
     * Current visitor country
     * @var string
     */
    const LOCATION_COUNTRY = 'sdk_country';

    /**
     * Current visitor latitude
     * @var float
     */
    const LOCATION_LAT = 'sdk_lat';

    /**
     * Current visitor longitude
     * @var float
     */
    const LOCATION_LONG = 'sdk_long';

    /**
     * Device public ip
     * @var string
     */
    const IP = 'sdk_ip';

    /**
     * OS name
     * @var string
     */
    const OS_NAME = 'sdk_osName';

    /**
     * OS version name
     * @var string
     */
    const OS_VERSION_NAME = 'sdk_osVersionName';

    /**
     * OS version code
     * @var float
     */
    const OS_VERSION_CODE = 'sdk_osVersionCode';

    /**
     * Carrier operator
     * @var string
     */
    const CARRIER_NAME = 'sdk_carrierName';

    /**
     * Internet connexion type : 4G, 5G, Fiber
     * @var string
     */
    const INTERNET_CONNECTION = 'sdk_internetConnection';

    /**
     * Customer app version name
     * @var string
     */
    const APP_VERSION_NAME = 'sdk_versionName';

    /**
     * Customer app version code
     * @var float
     */
    const APP_VERSION_CODE = 'sdk_versionCode';

    /**
     * Current customer app interface name
     * @var string
     */
    const INTERFACE_NAME = 'sdk_interfaceName';

    /**
     * Flagship SDK client name
     * @var string
     */
    const FLAGSHIP_CLIENT = 'fs_client';

    /**
     * Flagship SDK version name
     * @var string
     */
    const FLAGSHIP_VERSION = 'fs_version';

    /**
     * Current visitor id
     * @var string
     */
    const FLAGSHIP_VISITOR = 'fs_users';

    private static $predefinedContext = [
        self::DEVICE_LOCALE => "string",
        self::DEVICE_TYPE => "string",
        self::DEVICE_MODEL => "string",
        self::LOCATION_CITY => "string",
        self::LOCATION_REGION => "string",
        self::LOCATION_COUNTRY => "string",
        self::LOCATION_LAT => "float",
        self::LOCATION_LONG => "float",
        self::IP => "string",
        self::OS_NAME => "string",
        self::OS_VERSION_NAME => "string",
        self::OS_VERSION_CODE => "float",
        self::CARRIER_NAME => "string",
        self::INTERNET_CONNECTION => "string",
        self::APP_VERSION_NAME => "string",
        self::APP_VERSION_CODE => "float",
        self::INTERFACE_NAME => "string",
        self::FLAGSHIP_CLIENT => "string",
        self::FLAGSHIP_VERSION => "string",
        self::FLAGSHIP_VISITOR => "string"
        ];

    /**
     * @param $context string
     * @return string|null
     */
    public static function getType($context)
    {
        return isset(self::$predefinedContext[$context]) ? self::$predefinedContext[$context] : null;
    }
}
