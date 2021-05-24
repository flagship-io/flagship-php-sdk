<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\FlagshipConfig;
use Flagship\Model\Modification;
use Flagship\Traits\LogTrait;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\ConfigManager;
use JsonSerializable;

abstract class VisitorAbstract implements VisitorInterface, JsonSerializable
{
    use LogTrait;
    use ValidatorTrait;

    /**
     * @var FlagshipConfig
     */
    protected $config;
    /**
     * @var string
     */
    private $visitorId;
    /**
     * @var array
     */
    public $context = [];

    /**
     * @var Modification[]
     */
    protected $modifications = [];
    /**
     * @var ConfigManager
     */
    protected $configManager;
    /**
     * @var bool
     */
    protected $hasConsented = false;

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }

    /**
     * @param Modification[] $modifications
     * @return VisitorAbstract
     */
    public function setModifications($modifications)
    {
        $this->modifications = $modifications;
        return $this;
    }

    /**
     * @param ConfigManager $configManager
     * @return VisitorAbstract
     */
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
        return $this;
    }



    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * @param string $visitorId
     * @return VisitorAbstract
     */
    public function setVisitorId($visitorId)
    {
        if (empty($visitorId)) {
            $this->logError(
                $this->config,
                FlagshipConstant::VISITOR_ID_ERROR,
                [FlagshipConstant::TAG => __FUNCTION__]
            );
        } else {
            $this->visitorId = $visitorId;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
    * /**
     * Clear the current context and set a new context value
     *
     * @param  array $context : collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     * @return VisitorAbstract
     */
    public function setContext($context)
    {
        $this->context = [];
        $this->updateContextCollection($context);
        return $this;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return VisitorAbstract
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return array
     */
    public function getModifications()
    {
        return $this->modifications;
    }

    /**
     * @return VisitorStrategyAbstract
     */
    public function getStrategy()
    {
        return new DefaultStrategyAbstract($this);
    }
    /**
     * Return True or False if the visitor has consented for private data usage.
     * @return bool
     */
    public function hasConsented()
    {
        return $this->hasConsented;
    }

    /**
     * Set if visitor has consented for private data usage.
     * @param bool $hasConsented True if the visitor has consented false otherwise.
     */
    public function setConsent($hasConsented)
    {
        $this->hasConsented = $hasConsented;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'visitorId' => $this->getVisitorId(),
            'context' => $this->getContext(),
        ];
    }
}
