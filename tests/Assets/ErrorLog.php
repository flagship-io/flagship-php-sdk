<?php

namespace Flagship\Assets {
    class ErrorLog
    {
        public static $error = '';
    }
}

namespace Flagship\Utils {

    use Flagship\Assets\ErrorLog;

    function error_log($message, $message_type = null, $destination = null, $extra_headers = null)
    {
        ErrorLog::$error = $message;
    }
}
