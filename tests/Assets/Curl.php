<?php

namespace Flagship\Assets {
    class Curl
    {
        public static $curlResource = [];
        public static $response = '';
        public static $curlErrorCode = 0;
        public static $errorMessage = '';
        public static $curlHttpCodeInfo = '';
        public static $curlLastModifies = -1;
        public static $extension = true;
        public static $curlHttpContentType = "application/json";
    }
}

namespace Flagship\Utils {

    use Flagship\Assets\Curl;

    function extension_loaded()
    {
        return Curl::$extension;
    }

    function curl_init()
    {
        Curl::$curlResource['url'] = 'url';
        return Curl::$curlResource;
    }

    function curl_setopt($handle, $option, $value)
    {
        Curl::$curlResource[$option] = $value;
        return true;
    }

    function curl_exec($handle)
    {
        return Curl::$response;
    }

    function curl_errno($handle)
    {
        return Curl::$curlErrorCode;
    }

    function curl_error($handle)
    {
        return Curl::$errorMessage;
    }

    function curl_close($handle)
    {
    }

    function curl_getinfo($handle, $option)
    {
        switch ($option) {
            case CURLINFO_HTTP_CODE:
                $value = Curl::$curlHttpCodeInfo;
                break;
            case CURLINFO_FILETIME:
                $value = Curl::$curlLastModifies;
                break;
            default:
                $value = Curl::$curlHttpContentType;
                break;
        }
        return $value;
    }
}
