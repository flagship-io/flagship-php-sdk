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
     * Default request timeout
     */
    const REQUEST_TIME_OUT = 2000;

    /**
     * SDK language
     */
    const SDK_LANGUAGE = "PHP";

    /**
     * Decision api base url
     */
    const BASE_API_URL = "https://decision.flagship.io/v2";
    const URL_CAMPAIGNS      = 'campaigns';
    const URL_ACTIVATE_MODIFICATION = 'activate';


    const EXPOSE_ALL_KEYS = "exposeAllKeys";

    /**
     * SDK version
     */
    const SDK_VERSION = "v1";

    //Message Error
    const INITIALIZATION_PARAM_ERROR     = "Params 'envId' and 'apiKey' must not be null or empty.";
    const ERROR                          = "error";
    const CONTEXT_PARAM_ERROR            = "params 'key' must be a non null String, and 'value' must be one of the
        following types : String, Number, Boolean, JsonObject, JsonArray.";
    const GET_MODIFICATION_CAST_ERROR    = "Modification for key '%s' has a different type. Default value is returned.";
    const GET_MODIFICATION_MISSING_ERROR = "No modification for key '%s'. Default value is returned.";
    const GET_MODIFICATION_KEY_ERROR = "Key '%s' must not be null. Default value is returned.";
    const GET_MODIFICATION_ERROR     = "No modification for key '%s'.";
    const DECISION_MANAGER_MISSING_ERROR = "decisionManager must not be null." ;
    const TRACKER_MANAGER_MISSING_ERROR  = "trackerManager must not be null.";
    const CURL_LIBRARY_IS_NOT_LOADED = 'curl library is not loaded';

    //Messages Info
    const SDK_STARTED_INFO = "Flagship SDK (version: %s) READY";
    const FLAGSHIP_SDK ="Flagship SDK";

    //Process
    const PROCESS                       = 'process';
    const PROCESS_INITIALIZATION        = 'INITIALIZATION';
    const PROCESS_UPDATE_CONTEXT        = 'UPDATE CONTEXT';
    const PROCESS_GET_MODIFICATION      = 'GET MODIFICATION';
    const PROCESS_GET_MODIFICATION_INFO = 'GET MODIFICATION INFO';
    const PROCESS_NEW_VISITOR           = 'NEW VISITOR';
    const PROCESS_ACTIVE_MODIFICATION   = 'ACTIVE MODIFICATION';
    const PROCESS_SYNCHRONIZED_MODIFICATION = "SYNCHRONIZED MODIFICATION";

    //Api items

    const CUSTOMER_ENV_ID            = "cid";
    const VISITOR_ID                 = "vid";
    const VARIATION_GROUP_ID         = "caid";
    const VARIATION_ID               = "vaid";

}
