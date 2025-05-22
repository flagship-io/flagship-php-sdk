<?php

namespace Flagship;

use Exception;
use Flagship\Api\TrackingManager;
use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Config\FlagshipConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FSSdkStatus;
use Flagship\Enum\LogLevel;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Visitor\Visitor;
use Flagship\Visitor\VisitorBuilder;
use Flagship\Visitor\VisitorDelegate;
use Psr\Log\LoggerInterface;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class FlagshipTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logManagerMock;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->logManagerMock = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            '',
            false,
            true,
            true,
            [
             'error',
             'info',
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function containerInitialization(): Container
    {
        $container = new Container();

        $container->bind(
            'Flagship\Utils\HttpClientInterface',
            'Flagship\Utils\HttpClient'
        );
        if (version_compare(phpversion(), '8', '>=')) {
            $container->bind(
                'Psr\Log\LoggerInterface',
                'Flagship\Utils\FlagshipLogManager8'
            );
        } else {
            $container->bind(
                'Psr\Log\LoggerInterface',
                'Flagship\Utils\FlagshipLogManager'
            );
        }
        return $container;
    }

    /**
     * @throws ReflectionException
     */
    public function testInstance()
    {
        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance1 = $instanceMethod->invoke(null);
        $instance2 = $instanceMethod->invoke(null);

        //Test static method instance return an instance of Flagship\Flagship
        $this->assertInstanceOf("Flagship\Flagship", $instance1);
        $this->assertInstanceOf("Flagship\Flagship", $instance1);

        //Test static method instance return singleton of Flagship\Flagship
        $this->assertSame($instance1, $instance2);
    }

    /**
     * @throws ReflectionException
     */
    public function testStart()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_INITIALIZED, Flagship::getStatus());

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);
        $getConfigManager = Utils::getMethod($instance, 'getConfigManager');
        $configManager = $getConfigManager->invoke($instance);

        $this->assertInstanceOf('Flagship\Config\DecisionApiConfig', Flagship::getConfig());

        $this->assertSame($envId, Flagship::getConfig()->getEnvId());
        $this->assertSame($apiKey, Flagship::getConfig()->getApiKey());

        $this->assertInstanceOf('Flagship\Utils\ConfigManager', $configManager);
        $this->assertInstanceOf('Flagship\Decision\ApiManager', $configManager->getDecisionManager());
        $this->assertInstanceOf('Flagship\Api\TrackingManager', $configManager->getTrackingManager());
        $this->assertInstanceOf('Flagship\Config\DecisionApiConfig', $configManager->getConfig());

        $this->assertSame(Flagship::getConfig(), $configManager->getConfig());
    }

    /**
     * @throws ReflectionException
     */
    public function testStartWithoutConfig()
    {
        //Test Start Flagship without config argument

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);


        $envId = "end_id";
        $apiKey = "apiKey";

        //Test Start Flagship without config argument

        Flagship::start($envId, $apiKey);

        $configManager = Utils::getProperty("Flagship\Flagship", 'configManager')->getValue($instance);

        $this->assertInstanceOf('Flagship\Config\DecisionApiConfig', Flagship::getConfig());

        $this->assertSame($envId, Flagship::getConfig()->getEnvId());
        $this->assertSame($apiKey, Flagship::getConfig()->getApiKey());

        $this->assertSame(FSSdkStatus::SDK_INITIALIZED, Flagship::getStatus());

        $this->assertInstanceOf('Flagship\Decision\ApiManager', $configManager->getDecisionManager());
        $this->assertInstanceOf('Flagship\Api\TrackingManager', $configManager->getTrackingManager());
        $this->assertInstanceOf('Flagship\Config\DecisionApiConfig', $configManager->getConfig());

        $config = new BucketingConfig("http://127.0.0.1:3000");
        Flagship::start($envId, $apiKey, $config);
        $configManager = Utils::getProperty("Flagship\Flagship", 'configManager')->getValue($instance);
        $this->assertInstanceOf('Flagship\Decision\BucketingManager', $configManager->getDecisionManager());
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testStartWithLog()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");

        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder(
            'Flagship\Flagship'
        )->onlyMethods(['logInfo', 'logError', 'getContainer'])->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $flagshipMock->method('getContainer')->willReturn($this->containerInitialization());

        $flagshipMock->expects($this->once())->method('logInfo')->with(
            $config,
            sprintf(FlagshipConstant::SDK_STARTED_INFO, FlagshipConstant::SDK_VERSION),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
        );

        $flagshipMock->expects($this->never())->method('logError');

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_INITIALIZED, Flagship::getStatus());
    }

    public function testStartFailed()
    {
        //Test Start Flagship failed with empty envKey
        $envId = "";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_NOT_INITIALIZED, Flagship::getStatus());

        //Test Start Flagship failed with empty apiKey
        $envId = "envId";
        $apiKey = "";
        $config = new DecisionApiConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_NOT_INITIALIZED, Flagship::getStatus());

        //Test Start Flagship failed with empty apiKey
        $envId = "";
        $apiKey = "";
        $config = new DecisionApiConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_NOT_INITIALIZED, Flagship::getStatus());
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testStartFailedWithLog()
    {
        //Test Start Flagship failed with null envKey
        $envId = "";
        $apiKey = "apiKey";

        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");

        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder('Flagship\Flagship')->onlyMethods(['logInfo', 'logError', 'getContainer'])->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $flagshipMock->method('getContainer')->willReturn($this->containerInitialization());

        $flagshipMock->expects($this->once())->method('logError')->with(
            $config,
            FlagshipConstant::INITIALIZATION_PARAM_ERROR,
            [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
        );
        $flagshipMock->expects($this->never())->method('logInfo');

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_NOT_INITIALIZED, Flagship::getStatus());
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testStartFailedThrowException()
    {

        $envId = "envId";
        $apiKey = "apiKey";

        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");

        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder('Flagship\Flagship')->onlyMethods(['logInfo', 'logError', 'getContainer', 'setConfigManager'])->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $exception = new Exception();

        $flagshipMock->method('getContainer')->willReturn($this->containerInitialization());
        $flagshipMock->method('setConfigManager')->willThrowException($exception);

        $flagshipMock->expects($this->once())->method('logError')->with(
            $config,
            $exception->getMessage(),
            [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
        );
        $flagshipMock->expects($this->never())->method('logInfo');

        Flagship::start($envId, $apiKey, $config);
        $this->assertSame(FSSdkStatus::SDK_NOT_INITIALIZED, Flagship::getStatus());
    }

    public function testGetStatus()
    {
        //Test Status default is NO_READY
        $this->assertSame(FSSdkStatus::SDK_NOT_INITIALIZED, Flagship::getStatus());

        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";

        $config = new DecisionApiConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);
        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_INITIALIZED, Flagship::getStatus());
    }

    public function testNewVisitor()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);
        $visitorId = "visitorId";

        $visitor1 = Flagship::newVisitor($visitorId, true);
        $this->assertInstanceOf(VisitorBuilder::class, $visitor1);
    }

    public function testStatusCallback()
    {
        $config = $this->getMockForAbstractClass(
            FlagshipConfig::class,
            [],
            "",
            true,
            false,
            true,
            [ "getOnSdkStatusChanged"]
        );

        $callable = function ($status) {
            $this->assertSame(FSSdkStatus::SDK_INITIALIZED, $status);
        };

        $config->setLogLevel(LogLevel::ALL);
        $config->expects($this->exactly(1))->method("getOnSdkStatusChanged")->willReturn($callable);

        Flagship::start('envId', 'apiKey', $config);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetPanicModeStatus()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['post'], "", false);

        $visitorId = "visitorId";

        $body = [
                 "visitorId" => $visitorId,
                 "campaigns" => [],
                 "panic"     => true,
                ];

        $httpClientMock->expects($this->exactly(2))->method('post')->willReturnOnConsecutiveCalls(
            new HttpResponse(204, $body),
            new HttpResponse(204, [
                                   "visitorId" => $visitorId,
                                   "campaigns" => [],
                                  ])
        );

        $apiManager = new ApiManager($httpClientMock, $config);

        $trackingManager = new TrackingManager($config, $httpClientMock);

        $configManager = new ConfigManager($config, $apiManager, $trackingManager);

        $visitorId = 'Visitor_1';

        $containerGetMethod = function () use ($config, $apiManager, $trackingManager, $configManager, $visitorId) {
            $args = func_get_args();
            return match ($args[0]) {
                'Flagship\DecisionApiConfig' => $config,
                'Psr\Log\LoggerInterface' => $this->logManagerMock,
                'Flagship\Decision\ApiManager' => $apiManager,
                'Flagship\Api\TrackingManager' => $trackingManager,
                'Flagship\Utils\ConfigManager' => $configManager,
                'Flagship\Visitor\VisitorDelegate' => new VisitorDelegate(new Container(), $configManager, $visitorId,
                    false, [], true),
                'Flagship\Visitor\Visitor' => new Visitor($args[1][0]),
                default => null,
            };
        };

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->onlyMethods(['get'])->disableOriginalConstructor()->getMock();

        $containerMock->method('get')->will($this->returnCallback($containerGetMethod));

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'container', $containerMock);

        $config->setLogManager($this->logManagerMock);

        $envId = "end_id";
        $apiKey = "apiKey";

        Flagship::start($envId, $apiKey, $config);

        $visitor = Flagship::newVisitor('Visitor_1', false)->build();

        $visitor->fetchFlags();

        $this->assertSame(FSSdkStatus::SDK_PANIC, Flagship::getStatus());

        $visitor->fetchFlags();

        $this->assertSame(FSSdkStatus::SDK_INITIALIZED, Flagship::getStatus());
    }

    /**
     * @throws ReflectionException
     */
    public function testClose()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");

        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder(
            'Flagship\Flagship'
        )->onlyMethods(['logInfo', 'logError', 'getContainer', "getConfigManager"])->disableOriginalConstructor()->getMock();


        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false
        );

        $decisionApi = $this->getMockForAbstractClass(
            "Flagship\Decision\ApiManager",
            [],
            "",
            false
        );

        $configManagerMock = new ConfigManager(
            $config,
            $decisionApi,
            $trackingManagerMock
        );

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $trackingManagerMock->expects($this->once())->method("sendBatch");

        $flagshipMock->expects($this->exactly(1))->method("getConfigManager")->willReturn($configManagerMock);

        Flagship::Close();
    }

    /**
     * @throws ReflectionException
     */
    public function testCloseNull()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");

        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder(
            'Flagship\Flagship'
        )->onlyMethods(['logInfo', 'logError', 'getContainer', "getConfigManager"])->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $flagshipMock->expects($this->once())->method("getConfigManager")->willReturn(null);

        Flagship::Close();
    }
}
