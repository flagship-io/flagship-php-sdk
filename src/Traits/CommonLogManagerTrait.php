<?php

namespace Flagship\Traits;

use DateTime;
use Flagship\Enum\FlagshipConstant;

trait CommonLogManagerTrait
{
    public function getDateTime(): string
    {
        $date = new DateTime();
        return $date->format("Y-m-d H:i:s.u");
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array<mixed> $context
     * @return void
     */
    public function customLog(mixed $level, string $message, array $context = []): void
    {
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $formatDate = $this->getDateTime();
        $levelStr = is_scalar($level) || (is_object($level) && method_exists($level, '__toString'))
            ? (string) $level
            : gettype($level);
        $customMessage = "[$formatDate] [$flagshipSdk] [$levelStr] ";
        $contextString = $this->parseContextToString($context);
        error_log($customMessage . $contextString . " " . $message);
    }

    /**
     * @param array<mixed> $context
     * @return string
     */
    private function parseContextToString(array $context): string
    {
        $contextToString = "";

        if (count($context) > 0) {
            $contextToString .= '[';
        }
        foreach ($context as $key => $item) {
            $itemStr = is_scalar($item) || (is_object($item) && method_exists($item, '__toString'))
                ? (string) $item
                : json_encode($item);
            $contextToString .= "$key => $itemStr, ";
        }

        $contextToString = substr($contextToString, 0, -2);

        if (count($context) > 0) {
            $contextToString .= ']';
        }
        return $contextToString;
    }
}
