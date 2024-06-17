<?php

namespace Flagship;

use Exception;
use Flagship\Traits\Guid;
use Flagship\Utils\FlagshipLogManager8;
use Flagship\Utils\HttpClient;
use Psr\Log\LoggerInterface;
use Flagship\Traits\LogTrait;
use Flagship\Utils\Container;
use Flagship\Enum\FSSdkStatus;
use Flagship\Utils\MurmurHash;
use Flagship\Enum\DecisionMode;
use Flagship\Api\TrackingManager;
use Flagship\Decision\ApiManager;
use Flagship\Utils\ConfigManager;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Visitor\VisitorBuilder;
use Flagship\Visitor\VisitorAbstract;
use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\BucketingManager;
use Flagship\Utils\HttpClientInterface;

/**
 * Flagship main singleton.
 */
class Flagship
{
    use LogTrait;
    use Guid;

    /**
     * Flagship singleton instance
     *
     * @var Flagship
     */
    private static Flagship $instance;

    /**
     * Dependency injection container
     *
     * @var Container
     */
    private Container $container;
    /**
     * @var FlagshipConfig|null
     */
    private ?FlagshipConfig $config;

    /**
     * @var ConfigManager
     */
    private ConfigManager $configManager;
    /**
     * @var FSSdkStatus
     */
    private FSSdkStatus $status;

    /**
     * @var string
     */
    private string $flagshipInstanceId;

    /**
     * Flagship constructor.
     */
    private function __construct()
    {
        $this->status = FSSdkStatus::SDK_NOT_INITIALIZED;
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
        //private singleton clone
    }

    /**
     * Flagship singleton instance
     *
     * @return Flagship
     */
    protected static function getInstance(): Flagship
    {
        if (!self::$instance) {
            self::$instance = new Flagship();
            self::$instance->container = self::$instance->containerInitialization();
        }
        return self::$instance;
    }


    /**
     * Start the flagship SDK
     *
     * @param string $envId Environment id provided by Flagship.
     * @param string $apiKey Secure api key provided by Flagship.
     * @param FlagshipConfig|null $config : (optional) SDK configuration.
     */
    public static function start(string $envId, string $apiKey, ?FlagshipConfig $config = null): void
    {
        try {
            $flagship = self::getInstance();
            $flagship->flagshipInstanceId = $flagship->newGuid();
            $container = $flagship->getContainer();

            if (!$config) {
                $config = $container->get(DecisionApiConfig::class, [$envId, $apiKey]);
            }
            $config->setEnvId($envId);
            $config->setApiKey($apiKey);

            $flagship->setConfig($config);



            if (!$config->getLogManager()) {
                $logManager = $container->get(LoggerInterface::class);
                $config->setLogManager($logManager);
            }

            $httpClient = $container->get(HttpClientInterface::class);

            if ($config->getDecisionMode() === DecisionMode::BUCKETING) {
                $murmurHash = $container->get(MurmurHash::class);
                $decisionManager = $container->get(
                    BucketingManager::class,
                    [$httpClient, $config, $murmurHash]
                );
            } else {
                $decisionManager = $container->get(ApiManager::class, [$httpClient, $config]);
            }
            $decisionManager->setFlagshipInstanceId($flagship->flagshipInstanceId);

            //Will trigger setStatus method of Flagship if decisionManager want update status
            $decisionManager->setStatusChangedCallback([$flagship, 'setStatus']);

            $configManager = $container->get(ConfigManager::class);

            $configManager->setDecisionManager($decisionManager);

            $trackingManager = $container->get(
                TrackingManager::class,
                [$config, $httpClient, $flagship->flagshipInstanceId]
            );

            $configManager->setTrackingManager($trackingManager);

            $configManager->setConfig($config);

            $decisionManager->setTrackingManager($trackingManager);

            $flagship->setConfigManager($configManager);

            if (empty($envId) || empty($apiKey)) {
                $flagship->logError(
                    $config,
                    FlagshipConstant::INITIALIZATION_PARAM_ERROR,
                    [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
                );
                $flagship->setStatus(FSSdkStatus::SDK_NOT_INITIALIZED);
                return;
            }

            $flagship->setStatus(FSSdkStatus::SDK_INITIALIZED);

            $flagship->logInfo(
                $config,
                sprintf(FlagshipConstant::SDK_STARTED_INFO, FlagshipConstant::SDK_VERSION),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
            );
        } catch (Exception $exception) {
            self::getInstance()->setStatus(FSSdkStatus::SDK_NOT_INITIALIZED);

            self::getInstance()->logError(
                $config,
                $exception->getMessage(),
                [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
            );
        }
    }

    /**
     * This function initialize the dependency injection container
     *
     * @return Container
     * @throws Exception
     */
    private function containerInitialization(): Container
    {
        $newContainer = new Container();

        $newContainer->bind(
            HttpClientInterface::class,
            HttpClient::class
        );
        $newContainer->bind(
            LoggerInterface::class,
            FlagshipLogManager8::class
        );

        return $newContainer;
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    /**
     * @param ConfigManager $configManager
     * @return Flagship
     */
    protected function setConfigManager(ConfigManager $configManager): static
    {
        $this->configManager = $configManager;
        return $this;
    }


    /**
     * Return the current config set by the customer and used by the SDK.
     *
     * @return FlagshipConfig
     */
    public static function getConfig(): FlagshipConfig
    {
        return self::getInstance()->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return Flagship
     */
    protected function setConfig(FlagshipConfig $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Return current status of Flagship SDK.
     * @return FSSdkStatus
     */
    public static function getStatus(): FSSdkStatus
    {
        return self::getInstance()->status;
    }

    /**
     * Set Flagship SDK status
     *
     * @param FSSdkStatus $status FSSdkStatus
     * @return Flagship
     */
    public function setStatus(FSSdkStatus $status): static
    {
        $onSdkStatusChanged = $this->config?->getOnSdkStatusChanged();
        if ($onSdkStatusChanged && $this->status !== $status) {
            call_user_func($onSdkStatusChanged, $status);
        }
        VisitorAbstract::setSdkStatus($status);
        $this->status = $status;
        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * This method batches and sends all collected hits.
     * It should be called when your application (script) is about to terminate
     * or in the event of a crash to ensures that all collected data is sent and not lost.
     * @return void
     */
    public static function close(): void
    {
        $instance = self::getInstance();
        if (!$instance->getConfigManager()) {
            return;
        }
        $instance->getConfigManager()->getTrackingManager()->sendBatch();
    }

    /**
     * Initialize the builder and return a \Flagship\Visitor\VisitorBuilder.
     *
     * @param string|null $visitorId Unique visitor identifier. If null, the SDK will generate one.
     * @param bool $hasConsented Whether the visitor has given consent.
     * @return VisitorBuilder
     */
    public static function newVisitor(?string $visitorId, bool $hasConsented): VisitorBuilder
    {
        $instance = self::getInstance();
        return VisitorBuilder::builder(
            $visitorId,
            $hasConsented,
            $instance->getConfigManager(),
            $instance->getContainer(),
            $instance->flagshipInstanceId
        );
    }
}
