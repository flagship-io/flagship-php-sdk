<?php

namespace Flagship\Assets {

    class Round
    {
        public static $returnValue = 0;
    }
}

namespace Flagship\Hit {

    use Flagship\Assets\Round;

    function round($num, $precision = 0, $mode = PHP_ROUND_HALF_UP)
    {
        return Round::$returnValue;
    }
}
