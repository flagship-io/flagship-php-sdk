<?php

namespace Flagship\Visitor;

use Flagship\Utils\ConfigManager;
use Flagship\Utils\ContainerInterface;

/**
 * This class represents a Visitor builder.
 */
class VisitorBuilder
{
    /**
     * @var bool
     */
    private $isAuthenticated;
    /**
     * @var bool
     */
    private $hasConsented;

    /**
     * @var array
     */
    private $context;
    /**
     * @var string
     */
    private $visitorId;
    /**
     * @var ConfigManager
     */
    private $configManager;
    /**
     * @var ContainerInterface
     */
    private $dependencyIContainer;

    /**
     * @var string
     */
    private $flagshipInstance;


    /**
     * @var callable
     */
    private $onFetchFlagsStatusChanged;



    /**
     * @param string|null $visitorId
     * @param bool $hasConsented
     * @param ConfigManager $configManager
     * @param ContainerInterface $dependencyIContainer
     * @param string|null $flagshipInstance
     */
    private function __construct($visitorId, $hasConsented, ConfigManager $configManager, ContainerInterface $dependencyIContainer, $flagshipInstance)
    {
        $this->visitorId = $visitorId;
        $this->hasConsented = $hasConsented;
        $this->configManager = $configManager;
        $this->dependencyIContainer = $dependencyIContainer;
        $this->isAuthenticated = false;
        $this->context = [];
        $this->flagshipInstance = $flagshipInstance;
    }

    /**
     * Create a new visitor builder.
     *
     * @param string $visitorId The visitor identifier.
     * @param bool $hasConsented Set to true when the visitor has consented, false otherwise.
     * @param ConfigManager $configManager The configuration manager.
     * @param ContainerInterface $container The dependency injection container.

     * @param string|null $flagshipInstance The Flagship instance identifier.
     * @return VisitorBuilder
     */
    public static function builder($visitorId, $hasConsented, ConfigManager $configManager, ContainerInterface $container, $flagshipInstance)
    {
        return new VisitorBuilder($visitorId, $hasConsented, $configManager, $container, $flagshipInstance);
    }

    /**
     * Specify if the visitor is authenticated or anonymous.
     *
     * @param bool $isAuthenticated true for an authenticated visitor, false for an anonymous visitor.
     * @return VisitorBuilder
     */
    public function isAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
        return $this;
    }

    /**
     * Specify visitor initial context key / values used for targeting.
     * Context key must be String, and value type must be one of the following : Number, Boolean, String.
     * @param array $context : visitor context. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"].
     * @return VisitorBuilder
     */
    public function withContext(array $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Specify a callback function to be called when the status of the fetchFlags method changes.
     * The callback function should have the following signature: 
     * function(FetchFlagsStatusInterface $fetchFlagsStatus): void
     * @param callable $onFetchFlagsStatusChanged
     * @return VisitorBuilder
     */
    public function onFetchFlagsStatusChanged(callable $onFetchFlagsStatusChanged)
    {
        $this->onFetchFlagsStatusChanged = $onFetchFlagsStatusChanged;
        return $this;
    }

    /**
     * Create a new visitor.
     * @return VisitorInterface
     */
    public function build()
    {
        $visitorDelegate = $this->dependencyIContainer->get('Flagship\Visitor\VisitorDelegate', [
            $this->dependencyIContainer,
            $this->configManager,
            $this->visitorId,
            $this->isAuthenticated,
            $this->context,
            $this->hasConsented,
            $this->flagshipInstance,
            $this->onFetchFlagsStatusChanged
        ], true);

        $visitorDelegate->setFlagshipInstanceId($this->flagshipInstance);

        return $this->dependencyIContainer->get('Flagship\Visitor\Visitor', [$visitorDelegate], true);
    }
}
