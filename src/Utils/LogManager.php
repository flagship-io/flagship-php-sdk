<?php

namespace Flagship\Utils;

use Flagship\Interfaces\LogManagerInterface;

class LogManager implements LogManagerInterface
{

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = array())
    {
        // TODO: Implement emergency() method.
    }

    /**
     * @inheritDoc
     */
    public function alert($message, $context = null)
    {
        // TODO: Implement alert() method.
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = array())
    {
        // TODO: Implement critical() method.
    }

    /**
     * @inheritDoc
     */
    public function error($message, $context = null)
    {
        // TODO: Implement error() method.
    }

    /**
     * @inheritDoc
     */
    public function warning($message, $context = null)
    {
        // TODO: Implement warning() method.
    }

    /**
     * @inheritDoc
     */
    public function notice($message, $context = [])
    {
        // TODO: Implement notice() method.
    }

    /**
     * @inheritDoc
     */
    public function info($message, $context = [])
    {
        // TODO: Implement info() method.
    }

    /**
     * @inheritDoc
     */
    public function debug($message, $context = [])
    {
        // TODO: Implement debug() method.
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        // TODO: Implement log() method.
    }
}
