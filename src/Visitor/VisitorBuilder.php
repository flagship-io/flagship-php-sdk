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
     * @param string $visitorId : visitor unique identifier.
     * @param ConfigManager $configManager
     * @param ContainerInterface $dependencyIContainer
     */
    private function __construct($visitorId, $configManager, $dependencyIContainer, $flagshipInstance = null)
    {
        $this->visitorId = $visitorId;
        $this->configManager = $configManager;
        $this->dependencyIContainer =  $dependencyIContainer;
        $this->isAuthenticated =  false;
        $this->context = [];
        $this->hasConsented = true;
        $this->flagshipInstance = $flagshipInstance;
    }

    /**
     * @param $visitorId
     * @param $configManager
     * @param $container
     * @return VisitorBuilder
     */
    public static function builder($visitorId, $configManager, $container, $flagshipInstance = null)
    {
        return new VisitorBuilder($visitorId, $configManager, $container, $flagshipInstance);
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
     * Specify if the visitor has consented for personal data usage.
     * When false some features will be deactivated,
     * cache will be deactivated and cleared.
     *
     * @param bool $hasConsented Set to true when the visitor has consented, false otherwise.
     * @return VisitorBuilder
     */
    public function hasConsented($hasConsented)
    {
        $this->hasConsented = $hasConsented;
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
        ], true);

        return $this->dependencyIContainer->get('Flagship\Visitor\Visitor', [$visitorDelegate], true);
    }
}
