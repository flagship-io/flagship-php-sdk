<?php

namespace Flagship\Enum;

/**
 * Global SDK constants
 *
 * @package Flagship\Enum
 */
class FlagshipConstant
{

    /**
     * Default request timeout in second
     */
    const REQUEST_TIME_OUT = 2;

    /**
     * SDK language
     */
    const SDK_LANGUAGE = "PHP";

    /**
     * Decision api base url
     */
    const BASE_API_URL              = "https://decision.flagship.io/v2";
    const HIT_API_URL               = "https://ariane.abtasty.com";
    const URL_CAMPAIGNS             = 'campaigns';
    const URL_ACTIVATE_MODIFICATION = 'activate';

    const EXPOSE_ALL_KEYS = "exposeAllKeys";

    /**
     * SDK version
     */
    const SDK_VERSION = "v1";

    //Message Error
    const INITIALIZATION_PARAM_ERROR       = "Params 'envId' and 'apiKey' must not be null or empty.";
    const ERROR                            = "error";
    const CONTEXT_PARAM_ERROR              = "params 'key' must be a non null String, and 'value' must be one of the
        following types : String, Number, Boolean";
    const GET_MODIFICATION_CAST_ERROR      = "Modification for key '%s' has a different type. Default value is returned.";
    const GET_MODIFICATION_MISSING_ERROR   = "No modification for key '%s'. Default value is returned.";
    const GET_MODIFICATION_KEY_ERROR       = "Key '%s' must not be null. Default value is returned.";
    const GET_MODIFICATION_ERROR           = "No modification for key '%s'.";
    const DECISION_MANAGER_MISSING_ERROR   = "decisionManager must not be null.";
    const TRACKER_MANAGER_MISSING_ERROR    = "trackerManager must not be null.";
    const CURL_LIBRARY_IS_NOT_LOADED       = 'curl library is not loaded';
    const TYPE_ERROR                       = " '%s' must be a '%s'";
    const PANIC_MODE_ERROR                 = "'%s' deactivated while panic mode is on.";
    const VISITOR_ID_ERROR                 = "visitorId must not be null or empty";
    const METHOD_DEACTIVATED_ERROR         = "Method '%s' is deactivated while SDK status is: %s.";
    const METHOD_DEACTIVATED_CONSENT_ERROR = "Method '%s' is deactivated for visitor '%s': visitor did not consent.";

    //Messages Info
    const SDK_STARTED_INFO = "Flagship SDK (version: %s) READY";
    const FLAGSHIP_SDK     = "Flagship SDK";

    //Tag
    const TAG                           = 'TAG';
    const TAG_INITIALIZATION            = 'INITIALIZATION';
    const TAG_UPDATE_CONTEXT            = 'UPDATE CONTEXT';
    const TAG_GET_MODIFICATION          = 'GET MODIFICATION';
    const TAG_GET_MODIFICATION_INFO     = 'GET MODIFICATION INFO';
    const TAG_NEW_VISITOR               = 'NEW VISITOR';
    const TAG_ACTIVE_MODIFICATION       = 'ACTIVE MODIFICATION';
    const TAG_SYNCHRONIZED_MODIFICATION = "SYNCHRONIZED MODIFICATION";
    const TAG_SEND_HIT                  = "SEND HIT";

    //Api items

    const CUSTOMER_ENV_ID_API_ITEM    = "cid";
    const VISITOR_ID_API_ITEM         = "vid";
    const VARIATION_GROUP_ID_API_ITEM = "caid";
    const VARIATION_ID_API_ITEM       = "vaid";
    const DS_API_ITEM                 = 'ds';
    const T_API_ITEM                  = 't';
    const DL_API_ITEM                 = 'dl';
    const SDK_APP                     = "APP";
    const TID_API_ITEM                = "tid";
    const TA_API_ITEM                 = "ta";
    const TT_API_ITEM                 = "tt";
    const TC_API_ITEM                 = "tc";
    const TCC_API_ITEM                = "tcc";
    const ICN_API_ITEM                = "icn";
    const SM_API_ITEM                 = "sm";
    const PM_API_ITEM                 = "pm";
    const TR_API_ITEM                 = "tr";
    const TS_API_ITEM                 = "ts";
    const IN_API_ITEM                 = "in";
    const IC_API_ITEM                 = "ic";
    const IP_API_ITEM                 = "ip";
    const IQ_API_ITEM                 = "iq";
    const IV_API_ITEM                 = "iv";
    const EVENT_CATEGORY_API_ITEM     = "ec";
    const EVENT_ACTION_API_ITEM       = "ea";
    const EVENT_LABEL_API_ITEM        = "el";
    const EVENT_VALUE_API_ITEM        = "ev";

    const HEADER_X_API_KEY        = 'x-api-key';
    const HEADER_CONTENT_TYPE     = 'Content-Type';
    const HEADER_X_SDK_CLIENT     = 'x-sdk-client';
    const HEADER_X_SDK_VERSION    = 'x-sdk-version';
    const HEADER_APPLICATION_JSON = 'application/json';
    const TIMEOUT_TYPE_ERROR      = "timeout must be numeric and > 0";
    const LOG_LEVEL_ERROR         = "Loglevel value invalid, please use \Flagship\Enum\LogLevel ";
}
