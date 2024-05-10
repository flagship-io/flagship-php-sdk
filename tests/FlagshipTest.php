<?php

namespace Flagship;

use Exception;
use Flagship\Api\TrackingManager;
use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Decision\BucketingManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FSSdkStatus;
use Flagship\Enum\LogLevel;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\MurmurHash;
use Flagship\Visitor\Visitor;
use Flagship\Visitor\VisitorDelegate;
use Psr\Log\LoggerInterface;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class FlagshipTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    private $logManagerMock;

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
            ['error', 'info']
        );
    }

    public function containerInitialization()
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

    public function testStartWithoutConfig()
    {
        //Test Start Flagship without config argument

        $config = new DecisionApiConfig('confEnvId', 'ConfigApiKey');

        $apiManager = new ApiManager(new HttpClient(), $config);

        $trackingManager = new TrackingManager($config, new HttpClient());

        $configManager = new ConfigManager();

        $bucketingConfig = new BucketingConfig("http://127.0.0.1:3000");
        $bucketingManager = new BucketingManager(new HttpClient(), $bucketingConfig, new MurmurHash());

        $containerGetMethod = function () use (
            $config,
            $apiManager,
            $trackingManager,
            $configManager,
            $bucketingManager
) {
            $args = func_get_args();
            switch ($args[0]) {
                case 'Flagship\Config\DecisionApiConfig':
                    return $config;
                case 'Psr\Log\LoggerInterface':
                    return $this->logManagerMock;
                case 'Flagship\Decision\ApiManager':
                    return $apiManager;
                case 'Flagship\Api\TrackingManager':
                    return $trackingManager;
                case 'Flagship\Utils\ConfigManager':
                    return $configManager;
                case 'Flagship\Decision\BucketingManager':
                    return $bucketingManager;
                default:
                    return null;
            }
        };

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->setMethods(['get'])->disableOriginalConstructor()->getMock();

        $containerMock->method('get')->will($this->returnCallback($containerGetMethod));

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'container', $containerMock);

        $envId = "end_id";
        $apiKey = "apiKey";

        //Test Start Flagship without config argument

        Flagship::start($envId, $apiKey);

        $this->assertInstanceOf('Flagship\Config\DecisionApiConfig', Flagship::getConfig());

        $this->assertSame(Flagship::getConfig(), $configManager->getConfig());

        $this->assertSame($envId, Flagship::getConfig()->getEnvId());
        $this->assertSame($apiKey, Flagship::getConfig()->getApiKey());

        $this->assertSame(FSSdkStatus::SDK_INITIALIZED, Flagship::getStatus());

        $this->assertInstanceOf('Flagship\Utils\ConfigManager', $configManager);
        $this->assertInstanceOf('Flagship\Decision\ApiManager', $configManager->getDecisionManager());
        $this->assertInstanceOf('Flagship\Api\TrackingManager', $configManager->getTrackingManager());
        $this->assertInstanceOf('Flagship\Config\DecisionApiConfig', $configManager->getConfig());

        $config = new BucketingConfig("http://127.0.0.1:3000");
        Flagship::start($envId, $apiKey, $config);
        $this->assertInstanceOf('Flagship\Decision\BucketingManager', $configManager->getDecisionManager());
    }

    public function testStartWithLog()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");
        ;
        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder(
            'Flagship\Flagship'
        )->setMethods(['logInfo', 'logError', 'getContainer'])->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $flagshipMock->method('getContainer')->willReturn($this->containerInitialization());

        $flagshipMock->expects($this->once())->method('logInfo')
            ->with(
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

    public function testStartFailedWithLog()
    {
        //Test Start Flagship failed with null envKey
        $envId = null;
        $apiKey = "apiKey";

        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");
        ;
        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder('Flagship\Flagship')
            ->setMethods(['logInfo', 'logError', 'getContainer'])
            ->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $flagshipMock->method('getContainer')->willReturn($this->containerInitialization());

        $flagshipMock->expects($this->once())->method('logError')
            ->with(
                $config,
                FlagshipConstant::INITIALIZATION_PARAM_ERROR,
                [FlagshipConstant::TAG => FlagshipConstant::TAG_INITIALIZATION]
            );
        $flagshipMock->expects($this->never())->method('logInfo');

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertSame(FSSdkStatus::SDK_NOT_INITIALIZED, Flagship::getStatus());
    }

    public function testStartFailedThrowException()
    {

        $envId = "envId";
        $apiKey = "apiKey";

        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");
        ;
        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder('Flagship\Flagship')
            ->setMethods(['logInfo', 'logError', 'getContainer','setConfigManager'])
            ->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $exception = new Exception();

        $flagshipMock->method('getContainer')->willReturn($this->containerInitialization());
        $flagshipMock->method('setConfigManager')->willThrowException($exception);

        $flagshipMock->expects($this->once())->method('logError')
            ->with(
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
        $this->assertInstanceOf("Flagship\Visitor\VisitorBuilder", $visitor1);
    }

    public function testStatusCallback()
    {
        $config = $this->getMockForAbstractClass(
            "Flagship\Config\FlagshipConfig",
            [],
            "",
            false,
            false,
            true,
            [ "getOnSdkStatusChanged"]
        );

        $count = 0;
        $callable = function ($status) use (&$count) {
            $this->assertSame(FSSdkStatus::SDK_INITIALIZED, $status);
        };

        $config->setLogLevel(LogLevel::ALL);
        $config->expects($this->exactly(1))
            ->method("getOnSdkStatusChanged")
            ->willReturn($callable);

        Flagship::start('envId', 'apiKey', $config);
    }

    public function testGetPanicModeStatus()
    {
        $config = new DecisionApiConfig();

        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface', ['post'], "", false);

        $visitorId = "visitorId";

        $body = [
            "visitorId" => $visitorId,
            "campaigns" => [],
            "panic" => true
        ];

        $httpClientMock->expects($this->exactly(2))->method('post')
            ->willReturnOnConsecutiveCalls(
                new HttpResponse(204, $body),
                new HttpResponse(204, [
                    "visitorId" => $visitorId,
                    "campaigns" => [],
                ])
            );

        $apiManager = new ApiManager($httpClientMock, $config);

        $trackingManager = new TrackingManager($config, $httpClientMock);

        $configManager = new ConfigManager();
        $configManager->setConfig($config)
            ->setTrackingManager($trackingManager)
            ->setDecisionManager($apiManager);

        $visitorId = 'Visitor_1';

        $containerGetMethod = function () use ($config, $apiManager, $trackingManager, $configManager, $visitorId) {
            $args = func_get_args();
            switch ($args[0]) {
                case 'Flagship\DecisionApiConfig':
                    $returnValue = $config;
                    break;
                case 'Psr\Log\LoggerInterface':
                    $returnValue = $this->logManagerMock;
                    break;
                case 'Flagship\Decision\ApiManager':
                    $returnValue = $apiManager;
                    break;
                case 'Flagship\Api\TrackingManager':
                    $returnValue = $trackingManager;
                    break;
                case 'Flagship\Utils\ConfigManager':
                    $returnValue = $configManager;
                    break;
                case 'Flagship\Visitor\VisitorDelegate':
                    $returnValue = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);
                    break;
                case 'Flagship\Visitor\Visitor':
                    $returnValue =  new Visitor($args[1][0]);
                    break;
                default:
                    $returnValue = null;
            }
            return $returnValue ;
        };

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->setMethods(['get'])->disableOriginalConstructor()->getMock();

        $containerMock->method('get')
            ->will($this->returnCallback($containerGetMethod));

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

    public function testClose()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");
        ;
        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder(
            'Flagship\Flagship'
        )->setMethods(['logInfo', 'logError', 'getContainer', "getConfigManager"])
            ->disableOriginalConstructor()->getMock();

        $configManagerMock = $this->getMockBuilder("Flagship\Utils\ConfigManager")
            ->setMethods(["getTrackingManager"])
            ->getMock();

        $trackingManagerMock = $this->getMockForAbstractClass(
            "Flagship\Api\TrackingManagerAbstract",
            [],
            "",
            false
        );

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);


        $trackingManagerMock->expects($this->once())->method("sendBatch");

        $configManagerMock->expects($this->once())->method("getTrackingManager")->willReturn($trackingManagerMock);

        $flagshipMock->expects($this->exactly(2))->method("getConfigManager")
            ->willReturn($configManagerMock);

        Flagship::Close();
    }

    public function testCloseNull()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new DecisionApiConfig($envId, $apiKey);
        $logManager = $this->getMockForAbstractClass("Psr\Log\LoggerInterface");
        ;
        $config->setLogManager($logManager);

        $flagshipMock = $this->getMockBuilder(
            'Flagship\Flagship'
        )->setMethods(['logInfo', 'logError', 'getContainer', "getConfigManager"])
            ->disableOriginalConstructor()->getMock();

        $configManagerMock = $this->getMockBuilder("Flagship\Utils\ConfigManager")
            ->setMethods(["getTrackingManager"])
            ->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);


        $configManagerMock->expects($this->never())->method("getTrackingManager");

        $flagshipMock->expects($this->once())->method("getConfigManager")
            ->willReturn(null);

        Flagship::Close();
    }
}
