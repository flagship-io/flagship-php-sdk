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
    public const REQUEST_TIME_OUT = 2;

    public const DEFAULT_HIT_CACHE_TIME_MS = 14400000;

    /**
     * SDK language
     */
    public const SDK_LANGUAGE = "PHP";

    public const SDK = "SDK";

    public const TROUBLESHOOTING_VERSION = "1";

    /**
     * Decision api base url
     */
    public const BASE_API_URL = "https://decision.flagship.io/v2";

    public const THIRD_PARTY_SEGMENT_URL = 'https://api-data-connector.flagship.io/accounts/%s/segments/%s';
    public const URL_CAMPAIGNS = 'campaigns';
    public const URL_ACTIVATE_MODIFICATION = 'activate';
    public const HIT_EVENT_URL = 'https://events.flagship.io';

    public const TROUBLESHOOTING_HIT_URL = 'https://events.flagship.io/troubleshooting';
    public const ANALYTICS_HIT_URL = 'https://events.flagship.io/analytics';
    public const TROUBLESHOOTING_SENT_SUCCESS = 'Troubleshooting hit has been sent : %s';
    public const USAGE_HIT_HAS_BEEN_SENT_S = 'Usage hit has been sent : %s';
    public const TROUBLESHOOTING_HIT_ADDED_IN_QUEUE = 'The TROUBLESHOOTING HIT has been added in the pool queue : %s';
    public const USAGE_HIT_ADDED_IN_QUEUE = 'The USAGE HIT has been added in the pool queue : %s';
    public const ADD_TROUBLESHOOTING_HIT = 'ADD TROUBLESHOOTING HIT';
    public const ADD_USAGE_HIT = 'ADD USAGE HIT';

    public const SEND_TROUBLESHOOTING = 'SEND TROUBLESHOOTING';
    public const SEND_USAGE_HIT = 'SEND USAGE HIT';

    public const EXPOSE_ALL_KEYS = "exposeAllKeys";

    /**
     * SDK version
     */
    public const SDK_VERSION = "4.0.1";

    public const GET_FLAG = 'GET_FLAG';

    //Message Error
    public const INITIALIZATION_PARAM_ERROR = "Params 'envId' and 'apiKey' must not be null or empty.";
    public const ERROR = "error";
    public const CONTEXT_PARAM_ERROR = "params 'key' must be a non null String, and 'value' must be one of the
        following types : String, Number, Boolean";

    public const USER_EXPOSED_NO_FLAG_ERROR =  "For the visitor '%s', no flags were found with the key '%s'.
     As a result, user exposure will not be sent.";
    public const VISITOR_EXPOSED_VALUE_NOT_CALLED =
        "Visitor '%s', the flag with the key '%s' has been exposed without calling the `getValue` method first.";
    public const GET_FLAG_MISSING_ERROR =
        "For the visitor '%s', no flags were found with the key '%s'. 
        Therefore, the default value '%s' has been returned.";
    public const GET_FLAG_NOT_FOUND =
        "For the visitor '%s', no flags were found with the key '%s'. Therefore, an empty flag has been returned.";

    public const NO_FLAG_METADATA =
        "For the visitor '%s',no flags were found with the key '%s'. As a result, an empty metadata object is returned";
    public const GET_FLAG_CAST_ERROR =
        "For the visitor '%s', the flag with key '%s' has a different type compared to the default value. 
        Therefore, the default value '%s' has been returned.";
    public const USER_EXPOSED_CAST_ERROR =
        "For the visitor '%s', the flag with key '%s' has been exposed 
        despite having a different type compared to the default value";
    public const DECISION_MANAGER_MISSING_ERROR = "decisionManager must not be null.";
    public const TRACKER_MANAGER_MISSING_ERROR = "trackerManager must not be null.";
    public const CURL_LIBRARY_IS_NOT_LOADED = 'curl library is not loaded';
    public const TYPE_ERROR = " '%s' must be a '%s'";
    public const VISITOR_ID_ERROR = "visitorId must not be null or empty";
    public const METHOD_DEACTIVATED_ERROR = "Method '%s' is deactivated while SDK status is: %s.";
    public const METHOD_DEACTIVATED_SEND_CONSENT_ERROR = "Send consent hit is deactivated while SDK status is: %s.";
    public const METHOD_DEACTIVATED_CONSENT_ERROR =
        "Method '%s' is deactivated for visitor '%s': visitor did not consent.";
    public const METHOD_DEACTIVATED_BUCKETING_ERROR = "Method '%s' is deactivated on Bucketing mode.";
    public const FLAGSHIP_PREDEFINED_CONTEXT_ERROR = "Flagship predefined context %s must be %s";
    public const FLAGSHIP_VISITOR_NOT_AUTHENTIFICATE =  "Visitor is not authentificated yet";
    public const IS_NOT_CALLABLE_ERROR = "'%s' is not callable";
    //Messages Info
    public const SDK_STARTED_INFO = "Flagship SDK (version: %s) READY";
    public const FLAGSHIP_SDK = "Flagship SDK";


    //Tag
    public const TAG = 'TAG';
    public const TAG_INITIALIZATION = 'INITIALIZATION';
    public const TAG_UPDATE_CONTEXT = 'UPDATE CONTEXT';
    public const TAG_SEND_HIT = "SEND HIT";

    //Api items
    public const ANONYMOUS_ID = "aid";
    public const CUSTOMER_ENV_ID_API_ITEM = "cid";
    public const VISITOR_ID_API_ITEM = "vid";
    public const CUSTOMER_UID = "cuid";
    public const VARIATION_GROUP_ID_API_ITEM = "caid";
    public const VARIATION_ID_API_ITEM = "vaid";
    public const DS_API_ITEM = 'ds';
    public const T_API_ITEM = 't';
    public const DL_API_ITEM = 'dl';
    public const SDK_APP = "APP";
    public const TID_API_ITEM = "tid";
    public const TA_API_ITEM = "ta";
    public const TT_API_ITEM = "tt";
    public const TC_API_ITEM = "tc";
    public const TCC_API_ITEM = "tcc";
    public const ICN_API_ITEM = "icn";
    public const SM_API_ITEM = "sm";
    public const PM_API_ITEM = "pm";
    public const TR_API_ITEM = "tr";
    public const TS_API_ITEM = "ts";
    public const IN_API_ITEM = "in";
    public const IC_API_ITEM = "ic";
    public const IP_API_ITEM = "ip";
    public const IQ_API_ITEM = "iq";
    public const IV_API_ITEM = "iv";

    public const SL_API_ITEM = "sl";
    public const H_API_ITEM = 'h';
    public const EVENT_CATEGORY_API_ITEM = "ec";
    public const EVENT_ACTION_API_ITEM = "ea";
    public const EVENT_LABEL_API_ITEM = "el";
    public const EVENT_VALUE_API_ITEM = "ev";
    public const USER_IP_API_ITEM = "uip";
    public const SCREEN_RESOLUTION_API_ITEM = "sr";
    public const USER_LANGUAGE = "ul";
    public const SESSION_NUMBER = "sn";
    public const QT_API_ITEM = 'qt';

    public const HEADER_X_API_KEY = 'x-api-key';
    public const HEADER_CONTENT_TYPE = 'Content-Type';
    public const HEADER_X_SDK_CLIENT = 'x-sdk-client';
    public const HEADER_X_SDK_VERSION = 'x-sdk-version';
    public const HEADER_APPLICATION_JSON = 'application/json';
    public const TIMEOUT_TYPE_ERROR = "timeout must be numeric and > 0";

    public const FS_CLIENT = "fs_client";
    public const FS_VERSION = "fs_version";
    public const FS_USERS = "fs_users";

    public const FS_CONSENT = 'fs_consent';
    public const TRACKING_MANAGER = 'TRACKING_MANAGER';
    public const HIT_ADDED_IN_QUEUE = "The HIT has been added into the pool queue : '%s'";
    public const ACTIVATE_HIT_ADDED_IN_QUEUE = "The ACTIVATE hit has been added into the pool queue : '%s'";
    public const HIT_SENT_SUCCESS = "%s has been sent : %s";

    public const FETCH_THIRD_PARTY_SUCCESS = "%s has been fetched : %s";
    public const UNEXPECTED_ERROR_OCCURRED = '%s Unexpected Error occurred %s';
    public const PROCESS_CACHE = 'CACHE';
    public const SEND_BATCH = 'HIT BATCH';
    public const SEND_HIT = 'SEND_HIT';
    public const SEND_ACTIVATE = 'HIT ACTIVATE';

    public const HIT_CACHE_ERROR = '{0} threw an exception {1}';
    public const HIT_CACHE_LOADED = 'Hits cache has been loaded from database: %s';
    public const HIT_CACHE_SAVED = 'Hit cache has been saved into database : %s';
    public const HIT_DATA_FLUSHED = 'The following hit keys have been flushed from database : %s';
    public const ALL_HITS_FLUSHED = 'All hits cache has been flushed from database';
    public const BATCH = "batch";
    public const HIT_CACHE_FORMAT_ERROR = "Hit cache format error %s";
    public const FETCH_FLAGS_STARTED = 'visitor `%s` fetchFlags process is started';
    public const PROCESS_FETCHING_FLAGS = 'FETCH_FLAGS';
    public const FETCH_CAMPAIGNS_SUCCESS =
        'Visitor %s, anonymousId %s with context %s has just fetched campaigns %s in %s ms';
    public const FETCH_CAMPAIGNS_FROM_CACHE =
    'Visitor %s, anonymousId %s with context %s has just fetched campaigns from cache %s in %s ms';
    public const FETCH_FLAGS_FROM_CAMPAIGNS =
    'Visitor %s, anonymousId %s with context %s has just fetched flags %s from Campaigns';
    public const FLAG_USER_EXPOSED = 'FLAG_USER_EXPOSED';
    public const FLAG_VALUE = 'FLAG_VALUE';
    public const GET_FLAG_VALUE = 'Visitor %s, Flag for key %s returns value %s';
    public const LOG_FORMAT_MESSAGE = "message";
    public const LOG_FORMAT_URL = "url";
    public const LOG_FORMAT_REQUEST_BODY = 'REQUEST_BODY';
    public const LOG_FORMAT_REQUEST_HEADERS = 'REQUEST_HEADERS';
    public const LOG_FORMAT_RESPONSE_BODY = 'RESPONSE_BODY';
    public const LOG_FORMAT_RESPONSE_STATUS = 'RESPONSE_STATUS';
    public const LOG_FORMAT_DURATION = 'DURATION';
    public const ANALYTIC_HIT_ALLOCATION = 1;
    public const FLAGSHIP_VISITOR_ALREADY_AUTHENTICATE =
        "Visitor is already authenticated";
}
