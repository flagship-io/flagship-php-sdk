<?php

namespace Flagship;

use Exception;
use Flagship\Api\TrackingManager;
use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\FlagshipLogManager;
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
        $container->bind(
            'Psr\Log\LoggerInterface',
            'Flagship\Utils\FlagshipLogManager'
        );
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
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertTrue(Flagship::isReady());
        $this->assertSame(FlagshipStatus::READY, Flagship::getStatus());

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);
        $getConfigManager = Utils::getMethod($instance, 'getConfigManager');
        $configManager = $getConfigManager->invoke($instance);

        $this->assertInstanceOf('Flagship\FlagshipConfig', Flagship::getConfig());

        $this->assertSame($envId, Flagship::getConfig()->getEnvId());
        $this->assertSame($apiKey, Flagship::getConfig()->getApiKey());

        $this->assertInstanceOf('Flagship\Utils\ConfigManager', $configManager);
        $this->assertInstanceOf('Flagship\Decision\ApiManager', $configManager->getDecisionManager());
        $this->assertInstanceOf('Flagship\Api\TrackingManager', $configManager->getTrackingManager());
        $this->assertInstanceOf('Flagship\FlagshipConfig', $configManager->getConfig());

        $this->assertSame(Flagship::getConfig(), $configManager->getConfig());
    }

    public function testStartWithoutConfig()
    {
        //Test Start Flagship without config argument

        $config = new FlagshipConfig('confEnvId', 'ConfigApiKey');

        $apiManager = new ApiManager(new HttpClient());

        $trackingManager = new TrackingManager(new HttpClient());

        $configManager = new ConfigManager();

        $containerGetMethod = function () use ($config, $apiManager, $trackingManager, $configManager) {
            $args = func_get_args();
            switch ($args[0]) {
                case 'Flagship\FlagshipConfig':
                    return $config;
                case 'Psr\Log\LoggerInterface':
                    return $this->logManagerMock;
                case 'Flagship\Decision\ApiManager':
                    return $apiManager;
                case 'Flagship\Api\TrackingManager':
                    return $trackingManager;
                case 'Flagship\Utils\ConfigManager':
                    return $configManager;
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

        $this->assertInstanceOf('Flagship\FlagshipConfig', Flagship::getConfig());

        $this->assertSame(Flagship::getConfig(), $configManager->getConfig());

        $this->assertSame($envId, Flagship::getConfig()->getEnvId());
        $this->assertSame($apiKey, Flagship::getConfig()->getApiKey());

        $this->assertTrue(Flagship::isReady());

        $this->assertSame(FlagshipStatus::READY, Flagship::getStatus());

        $this->assertInstanceOf('Flagship\Utils\ConfigManager', $configManager);
        $this->assertInstanceOf('Flagship\Decision\ApiManager', $configManager->getDecisionManager());
        $this->assertInstanceOf('Flagship\Api\TrackingManager', $configManager->getTrackingManager());
        $this->assertInstanceOf('Flagship\FlagshipConfig', $configManager->getConfig());
    }

    public function testStartWithLog()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new FlagshipConfig($envId, $apiKey);
        $logManager = new FlagshipLogManager();
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
        $this->assertTrue(Flagship::isReady());
        $this->assertSame(FlagshipStatus::READY, Flagship::getStatus());
    }

    public function testStartFailed()
    {
        //Test Start Flagship failed with empty envKey
        $envId = "";
        $apiKey = "apiKey";
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        $callback = function ($status) {
            echo $status;
        };
        $config->setStatusChangedCallable($callback);
        $this->expectOutputString('10'); //Callback status STARTING then NOT_INITIALIZED
        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_INITIALIZED, Flagship::getStatus());

        //Test Start Flagship failed with empty apiKey
        $envId = "envId";
        $apiKey = "";
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        $this->expectOutputString('10'); //Callback status STARTING then NOT_INITIALIZED
        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());

        //Test Start Flagship failed with empty apiKey
        $envId = "";
        $apiKey = "";
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        $this->expectOutputString('10'); //Callback status STARTING then NOT_INITIALIZED
        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());
    }

    public function testStartFailedWithLog()
    {
        //Test Start Flagship failed with null envKey
        $envId = null;
        $apiKey = "apiKey";

        $config = new FlagshipConfig($envId, $apiKey);
        $logManager = new FlagshipLogManager();
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
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());
    }

    public function testStartFailedThrowException()
    {

        $envId = "envId";
        $apiKey = "apiKey";

        $config = new FlagshipConfig($envId, $apiKey);
        $logManager = new FlagshipLogManager();
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

        $callback = function ($status) {
            echo $status;
        };

        $config->setStatusChangedCallable($callback);

        $this->expectOutputString('10'); //Callback status STARTING then NOT_INITIALIZED
        Flagship::start($envId, $apiKey, $config);
    }

    public function testGetStatus()
    {
        //Test Status default is NO_READY
        $this->assertSame(FlagshipStatus::NOT_INITIALIZED, Flagship::getStatus());

        //Test FlagshipConfig is null
        $this->assertFalse(Flagship::isReady());

        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";

        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);
        $this->assertSame($config, Flagship::getConfig());
        $this->assertTrue(Flagship::isReady());
        $this->assertSame(FlagshipStatus::READY, Flagship::getStatus());
    }

    public function testIsReady()
    {
        //Test Flagship instance is null
        $this->assertFalse(Flagship::isReady());
    }

    public function testNewVisitor()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $context = ['age' => 20];
        $visitorId = "visitorId";
        $visitor1 = Flagship::newVisitor($visitorId, $context);
        $this->assertInstanceOf("Flagship\Visitor", $visitor1);
        $this->assertSame($context, $visitor1->getContext());
    }

    public function testNewVisitorFailed()
    {
        //Test Start Flagship with a empty envId
        $envId = "";
        $apiKey = "apiKey";
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $context = ['age' => 20];
        $visitorId = "visitorId";
        $visitor1 = Flagship::newVisitor($visitorId, $context);
        $this->assertSame(null, $visitor1);
    }

    public function testNewVisitorFailedWithoutStart()
    {
        //Test Start Flagship
        $context = ['age' => 20];
        $visitorId = "visitorId";

        $visitor1 = Flagship::newVisitor($visitorId, $context);

        $this->assertSame(null, $visitor1);
    }

    public function testStatusCallback()
    {
        $config = new FlagshipConfig();
        $callback = function ($status) {
            echo $status;
        };
        $config->setStatusChangedCallable($callback);
        $this->expectOutputString('14'); //Callback status STARTING then READY
        Flagship::start('envId', 'apiKey', $config);
    }

    public function testGetPanicModeStatus()
    {
        $config = new FlagshipConfig();

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

        $apiManager = new ApiManager($httpClientMock);

        $trackingManager = new TrackingManager(new HttpClient());

        $configManager = new ConfigManager();

        $visitorId = 'Visitor_1';

        $containerGetMethod = function () use ($config, $apiManager, $trackingManager, $configManager, $visitorId) {
            $args = func_get_args();
            switch ($args[0]) {
                case 'Flagship\FlagshipConfig':
                    return $config;
                case 'Psr\Log\LoggerInterface':
                    return $this->logManagerMock;
                case 'Flagship\Decision\ApiManager':
                    return $apiManager;
                case 'Flagship\Api\TrackingManager':
                    return $trackingManager;
                case 'Flagship\Utils\ConfigManager':
                    return $configManager;
                case 'Flagship\Visitor\VisitorDelegate':
                    return new VisitorDelegate(new Container(), $configManager, $visitorId, []);
                default:
                    return null;
            }
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

        $visitor = Flagship::newVisitor('Visitor_1');

        $visitor->synchronizedModifications();

        $this->assertSame(FlagshipStatus::READY_PANIC_ON, Flagship::getStatus());

        $visitor->synchronizedModifications();

        $this->assertSame(FlagshipStatus::READY, Flagship::getStatus());
    }
}
