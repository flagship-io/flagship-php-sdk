<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Flagship;
use Flagship\FlagshipConfig;
use Flagship\Model\Modification;
use Flagship\Traits\LogTrait;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
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
     * @var callable
     */
    protected $getStrategyCallable;
    /**
     * @var Container
     */
    private $dependencyIContainer;

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
    protected function getStrategy()
    {
        // TODO tomorrow test this function
        if (Flagship::getStatus() === FlagshipStatus::READY_PANIC_ON) {
            $strategy = $this->getDependencyIContainer()->get("Flagship\Visitor\PanicStrategy", [$this], true);
        } elseif (Flagship::getStatus() === FlagshipStatus::NOT_INITIALIZED) {
            $strategy = $this->getDependencyIContainer()->get("Flagship\Visitor\NotReadyStrategy", [$this], true);
        } elseif (!$this->hasConsented()) {
            $strategy = $this->getDependencyIContainer()->get("Flagship\Visitor\NoConsentStrategy", [$this], true);
        } else {
            $strategy = $this->getDependencyIContainer()->get("Flagship\Visitor\DefaultStrategy", [$this], true);
        }
        return $strategy;
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
     * @return Container
     */
    public function getDependencyIContainer()
    {
        return $this->dependencyIContainer;
    }

    public function setDependencyIContainer(Container $dependencyIContainer)
    {
        $this->dependencyIContainer = $dependencyIContainer;
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
