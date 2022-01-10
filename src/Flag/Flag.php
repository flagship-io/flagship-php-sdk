<?php

namespace Flagship\Flag;

use Flagship\Model\FlagDTO;
use Flagship\Visitor\VisitorAbstract;

class Flag implements FlagInterface
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var VisitorAbstract
     */
    private $visitorDelegate;
    /**
     * @var FlagDTO
     */
    private $flagDTO;
    /**
     * @var mixed
     */
    private $defaultValue;

    /***
     * @param string $key
     * @param VisitorAbstract $visitorDelegate
     * @param FlagDTO $flagDTO
     * @param mixed $defaultValue
     */
    public function __construct($key, VisitorAbstract $visitorDelegate, FlagDTO $flagDTO, $defaultValue)
    {
        $this->key = $key;
        $this->visitorDelegate = $visitorDelegate;
        $this->flagDTO = $flagDTO;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @inheritDoc
     */
    public function value($userExposed)
    {
        // TODO: Implement value() method.
    }

    /**
     * @inheritDoc
     */
    public function exists()
    {
        // TODO: Implement exists() method.
    }

    /**
     * @inheritDoc
     */
    public function userExposed()
    {
        // TODO: Implement userExposed() method.
    }

    /**
     * @inheritDoc
     */
    public function getMetadata()
    {
        // TODO: Implement getMetadata() method.
    }
}
