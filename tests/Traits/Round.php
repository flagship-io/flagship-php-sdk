<?php

namespace Flagship\Traits {

    class Round
    {
        public static $returnValue = 0;
    }
}

namespace Flagship\Traits {

    function round($num, $precision = 0, $mode = PHP_ROUND_HALF_UP)
    {
        return Round::$returnValue;
    }
}
