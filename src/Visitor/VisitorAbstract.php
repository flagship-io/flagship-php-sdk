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
     * @var array
     */
    public $campaigns = [];

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var boolean
     */
    public $hasConsented = false;

    /**
     * @var ContainerInterface
     */
    private $dependencyIContainer;

    /**
     * @var array
     */
    public $visitorCache;

    /**
     * @var string
     */
    private $flagSyncStatus;


    public function __construct()
    {
        $this->visitorCache = [];
    }

    /**
     * @return string
     */
    public function getFlagSyncStatus()
    {
        return $this->flagSyncStatus;
    }

    /**
     * @param string $flagSyncStatus
     * @return VisitorAbstract
     */
    public function setFlagSyncStatus($flagSyncStatus)
    {
        $this->flagSyncStatus = $flagSyncStatus;
        return $this;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }//end getConfigManager()


    /**
     * @deprecated use setFlagsDTO instead
     * @param      FlagDTO[] $modifications
     * @return     VisitorAbstract
     */
    public function setModifications($modifications)
    {
        $this->flagsDTO = $modifications;
        return $this;
    }//end setModifications()


    /**
     * @deprecated use getFlagsDTO instead
     * @return     array
     */
    public function getModifications()
    {
        return $this->flagsDTO;
    }//end getModifications()


    /**
     * @param  FlagDTO[] $flagsDTO
     * @return VisitorAbstract
     */
    public function setFlagsDTO($flagsDTO)
    {
        $this->flagsDTO = $flagsDTO;
        return $this;
    }//end setFlagsDTO()


    /**
     * @return FlagDTO[]
     */
    public function getFlagsDTO()
    {
        return $this->flagsDTO;
    }//end getFlagsDTO()


    /**
     * @param  ConfigManager $configManager
     * @return VisitorAbstract
     */
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
        return $this;
    }//end setConfigManager()


    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }//end getVisitorId()


    /**
     * @param  string $visitorId
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
    }//end setVisitorId()


    /**
     * @return string
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }//end getAnonymousId()


    /**
     * @param  string $anonymousId
     * @return VisitorAbstract
     */
    public function setAnonymousId($anonymousId)
    {
        $this->anonymousId = $anonymousId;
        return $this;
    }//end setAnonymousId()


    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }//end getContext()


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
    }//end setContext()


    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }//end getConfig()


    /**
     * @param  FlagshipConfig $config
     * @return VisitorAbstract
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }//end setConfig()


    /**
     * @return VisitorStrategyAbstract
     */
    protected function getStrategy()
    {
        if (Flagship::getStatus() === FlagshipStatus::NOT_INITIALIZED) {
            $strategy = $this->getDependencyIContainer()->get('Flagship\Visitor\NotReadyStrategy', [$this], true);
        } elseif (Flagship::getStatus() === FlagshipStatus::READY_PANIC_ON) {
            $strategy = $this->getDependencyIContainer()->get('Flagship\Visitor\PanicStrategy', [$this], true);
        } elseif (!$this->hasConsented()) {
            $strategy = $this->getDependencyIContainer()->get('Flagship\Visitor\NoConsentStrategy', [$this], true);
        } else {
            $strategy = $this->getDependencyIContainer()->get('Flagship\Visitor\DefaultStrategy', [$this], true);
        }

        return $strategy;
    }//end getStrategy()


    /**
     * Return True or False if the visitor has consented for private data usage.
     *
     * @return boolean
     */
    public function hasConsented()
    {
        return $this->hasConsented;
    }//end hasConsented()


    /**
     * Set if visitor has consented for private data usage.
     *
     * @param  $hasConsented True if the visitor has consented false otherwise.
     * @return void
     */
    public function setConsent($hasConsented)
    {
        $this->hasConsented = $hasConsented;
        $this->getStrategy()->setConsent($hasConsented);
    }//end setConsent()


    /**
     * @return ContainerInterface
     */
    public function getDependencyIContainer()
    {
        return $this->dependencyIContainer;
    }//end getDependencyIContainer()


    /**
     * @param  ContainerInterface $dependencyIContainer
     * @return void
     */
    public function setDependencyIContainer(ContainerInterface $dependencyIContainer)
    {
        $this->dependencyIContainer = $dependencyIContainer;
    }//end setDependencyIContainer()


    /**
     * @inheritDoc
     * @return     mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'visitorId'  => $this->getVisitorId(),
            'context'    => $this->getContext(),
            'hasConsent' => $this->hasConsented(),
        ];
    }//end jsonSerialize()
}//end class
