<?php

namespace Flagship\Api {

    class Round
    {
        public static $returnValue = 0;
    }
}

namespace Flagship\Api {

    function round($num, $precision = 0, $mode = PHP_ROUND_HALF_UP)
    {
        return Round::$returnValue;
    }
}
