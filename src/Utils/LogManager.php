<?php

namespace Flagship\Utils;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\LogLevel;
use Psr\Log\LoggerInterface;

class LogManager implements LoggerInterface
{

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $customMessage = "[$flagshipSdk] [$level] $message ";
        $contextString = $this->parseContextToString($context);
        error_log($customMessage . $contextString);
    }

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
