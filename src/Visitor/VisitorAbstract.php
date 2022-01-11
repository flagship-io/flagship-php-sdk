<?php

namespace Flagship\Visitor;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Flagship;
use Flagship\Model\FlagDTO;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\ContainerInterface;
use JsonSerializable;

abstract class VisitorAbstract implements VisitorInterface, JsonSerializable, VisitorFlagInterface
{
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
     * @var string
     */
    private $anonymousId;
    /**
     * @var array
     */
    public $context = [];

    /**
     * @var FlagDTO[]
     */
    protected $flagsDTO = [];
    /**
     * @var ConfigManager
     */
    protected $configManager;
    /**
     * @var bool
     */
    public $hasConsented = false;

    /**
     * @var ContainerInterface
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
     * @deprecated use setFlagsDTO instead
     * @param FlagDTO[] $modifications
     * @return VisitorAbstract
     */
    public function setModifications($modifications)
    {
        $this->flagsDTO = $modifications;
        return $this;
    }
    /**
     * @deprecated use getFlagsDTO instead
     * @return array
     */
    public function getModifications()
    {
        return $this->flagsDTO;
    }

    /**
     * @param FlagDTO[] $flagsDTO
     * @return VisitorAbstract
     */
    public function setFlagsDTO($flagsDTO)
    {
        $this->flagsDTO = $flagsDTO;
        return $this;
    }

    /**
     * @return FlagDTO[]
     */
    public function getFlagsDTO()
    {
        return $this->flagsDTO;
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
     * @return string
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }

    /**
     * @param string $anonymousId
     * @return VisitorAbstract
     */
    public function setAnonymousId($anonymousId)
    {
        $this->anonymousId = $anonymousId;
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
     * @return VisitorStrategyAbstract
     */
    protected function getStrategy()
    {
        if (Flagship::getStatus() === FlagshipStatus::NOT_INITIALIZED) {
            $strategy = $this->getDependencyIContainer()->get("Flagship\Visitor\NotReadyStrategy", [$this], true);
        } elseif (Flagship::getStatus() === FlagshipStatus::READY_PANIC_ON) {
            $strategy = $this->getDependencyIContainer()->get("Flagship\Visitor\PanicStrategy", [$this], true);
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
        $this->getStrategy()->setConsent($hasConsented);
    }

    /**
     * @return ContainerInterface
     */
    public function getDependencyIContainer()
    {
        return $this->dependencyIContainer;
    }

    public function setDependencyIContainer(ContainerInterface $dependencyIContainer)
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
            'hasConsent' => $this->hasConsented()
        ];
    }
}
