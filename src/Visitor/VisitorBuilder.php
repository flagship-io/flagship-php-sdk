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
    private bool $isAuthenticated;
    /**
     * @var bool
     */
    private bool $hasConsented;

    /**
     * @var array
     */
    private array $context;
    /**
     * @var string
     */
    private string $visitorId;
    /**
     * @var ConfigManager
     */
    private ConfigManager $configManager;
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $dependencyIContainer;

    /**
     * @var string
     */
    private string $flagshipInstance;


    /**
     * @var callable
     */
    private $onFetchFlagsStatusChanged;



    /**
     * @param string $visitorId
     * @param bool $hasConsented
     * @param ConfigManager $configManager
     * @param ContainerInterface $dependencyIContainer
     * @param string|null $flagshipInstance
     */
    private function __construct(
        string $visitorId,
        bool $hasConsented,
        ConfigManager $configManager,
        ContainerInterface $dependencyIContainer,
        ?string $flagshipInstance
    ) {
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
    public static function builder(
        string $visitorId,
        bool $hasConsented,
        ConfigManager $configManager,
        ContainerInterface $container,
        ?string $flagshipInstance
    ): VisitorBuilder {
        return new VisitorBuilder($visitorId, $hasConsented, $configManager, $container, $flagshipInstance);
    }

    /**
     * Specify if the visitor is authenticated or anonymous.
     *
     * @param bool $isAuthenticated true for an authenticated visitor, false for an anonymous visitor.
     * @return VisitorBuilder
     */
    public function setIsAuthenticated(bool $isAuthenticated): static
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
    public function setContext(array $context): static
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
    public function setOnFetchFlagsStatusChanged(callable $onFetchFlagsStatusChanged): static
    {
        $this->onFetchFlagsStatusChanged = $onFetchFlagsStatusChanged;
        return $this;
    }

    /**
     * Create a new visitor.
     * @return VisitorInterface
     */
    public function build(): VisitorInterface
    {
        $visitorDelegate = $this->dependencyIContainer->get(VisitorDelegate::class, [
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

        return $this->dependencyIContainer->get(Visitor::class, [$visitorDelegate], true);
    }
}
