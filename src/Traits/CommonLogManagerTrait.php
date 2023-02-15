<?php

namespace Flagship\Traits;

use DateTime;
use Flagship\Enum\FlagshipConstant;

trait CommonLogManagerTrait
{
    public function getDateTime()
    {
        $date = new DateTime();
        return $date->format("Y-m-d H:i:s.u");
    }
    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return void
     */
    public function customLog($level, $message, array $context = [])
    {
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $formatDate = $this->getDateTime();
        $customMessage = "[$formatDate] [$flagshipSdk] [$level] ";
        $contextString = $this->parseContextToString($context);
        error_log($customMessage . $message . " " . $contextString);
    }

    /**
     * @param $context
     * @return string
     */
    private function parseContextToString($context)
    {
        $contextToString = "";

        if (count($context) > 0) {
            $contextToString .= '[';
        }
        foreach ($context as $key => $item) {
            $contextToString .= "$key => $item, ";
        }

        $contextToString = substr($contextToString, 0, -2);

        if (count($context) > 0) {
            $contextToString .= ']';
        }
        return $contextToString;
    }
}
