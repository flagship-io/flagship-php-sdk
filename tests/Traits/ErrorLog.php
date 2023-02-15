<?php

namespace Flagship\Traits {
    class ErrorLog
    {
        public static $error = '';
    }
}

namespace Flagship\Traits {
    function error_log($message, $message_type = null, $destination = null, $extra_headers = null)
    {
        ErrorLog::$error = $message;
    }
}
