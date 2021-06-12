<?php

namespace Flagship\Enum;

class FlagshipContext
{
    /**
     * Current device locale
     * @var string
     */
    const DEVICE_LOCALE = '{"key":"sdk_deviceLanguage", "type":"string"}';
    /**
     * Current device type  tablet, pc, server, iot, other
     * @var string
     */
    const DEVICE_TYPE = '{"key":"sdk_deviceType", "type":"string"}';
    /**
     * Current device model
     * @var string
     */
    const DEVICE_MODEL = '{"key":"sdk_deviceModel", "type":"string"}';
    /**
     * Current visitor city
     * @var string
     */
    const LOCATION_CITY = '{"key":"sdk_city", "type":"string"}';
    /**
     * Current visitor region
     * @var string
     */
    const LOCATION_REGION = '{"key":"sdk_region", "type":"string"}';

    /**
     * Current visitor country
     * @var string
     */
    const LOCATION_COUNTRY = '{"key":"sdk_country", "type":"string"}';

    /**
     * Current visitor latitude
     * @var float
     */
    const LOCATION_LAT = '{"key":"sdk_lat", "type":"float"}';

    /**
     * Current visitor longitude
     * @var float
     */
    const LOCATION_LONG = '{"key":"sdk_long", "type":"float"}';

    /**
     * Device public ip
     * @var string
     */
    const IP = '{"key":"sdk_ip", "type":"string"}';

    /**
     * OS name
     * @var string
     */
    const OS_NAME = '{"key":"sdk_osName", "type":"string"}';

    /**
     * OS version name
     * @var string
     */
    const OS_VERSION_NAME = '{"key":"sdk_osVersionName", "type":"string"}';

    /**
     * OS version code
     * @var float
     */
    const OS_VERSION_CODE = '{"key":"sdk_osVersionCode", "type":"float"}';

    /**
     * Carrier operator
     * @var string
     */
    const CARRIER_NAME = '{"key":"sdk_carrierName", "type":"string"}';

    /**
     * Internet connexion type : 4G, 5G, Fiber
     * @var string
     */
    const INTERNET_CONNECTION = '{"key":"sdk_internetConnection", "type":"string"}';

    /**
     * Customer app version name
     * @var string
     */
    const APP_VERSION_NAME = '{"key":"sdk_versionName", "type":"string"}';

    /**
     * Customer app version code
     * @var float
     */
    const APP_VERSION_CODE = '{"key":"sdk_versionCode", "type":"float"}';

    /**
     * Current customer app interface name
     * @var string
     */
    const INTERFACE_NAME = '{"key":"sdk_interfaceName", "type":"string"}';

    /**
     * Flagship SDK client name
     * @var string
     */
    const FLAGSHIP_CLIENT = '{"key":"fs_client", "type":"string"}';

    /**
     * Flagship SDK version name
     * @var string
     */
    const FLAGSHIP_VERSION = '{"key":"fs_version", "type":"string"}';

    /**
     * Current visitor id
     * @var string
     */
    const FLAGSHIP_VISITOR = '{"key":"fs_users", "type":"string"}';
}
