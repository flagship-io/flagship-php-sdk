<?php

namespace Flagship;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Utils\LogManager;
use Flagship\Utils\LogManagerInterface;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class FlagshipTest extends TestCase
{
    /**
     * @var LogManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $logManagerMock;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->logManagerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['error','info']
        );
    }

    public function setUp()
    {
        parent::setUp();

        //Set Flagship singleton to null
        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);
        Utils::setPrivateProperty($instance, 'instance', null);
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
    }

    public function testStartWithoutConfig()
    {
        //Test Start Flagship without config argument

        $config = new FlagshipConfig('confEnvId', 'ConfigApiKey');

        $containerGetMethod = function () use ($config) {
            $args = func_get_args();
            switch ($args[0]) {
                case 'Flagship\FlagshipConfig':
                    return $config;
                case 'Flagship\Utils\LogManager':
                    return $this->logManagerMock;
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

        $this->assertSame($envId, Flagship::getConfig()->getEnvId());
        $this->assertSame($apiKey, Flagship::getConfig()->getApiKey());

        $this->assertTrue(Flagship::isReady());

        $this->assertSame(FlagshipStatus::READY, Flagship::getStatus());
    }

    public function testStartWithLog()
    {
        //Test Start Flagship
        $envId = "end_id";
        $apiKey = "apiKey";
        $config = new FlagshipConfig($envId, $apiKey);
        $logManager = new LogManager();
        $config->setLogManager($logManager);

        $flagshipStub = $this->getMockBuilder(
            'Flagship\Flagship'
        )->setMethods(['logInfo','logError'])->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipStub);

        $flagshipStub->expects($this->once())->method('logInfo')
            ->with(
                $config->getLogManager(),
                sprintf(FlagshipConstant::SDK_STARTED_INFO, FlagshipConstant::SDK_VERSION),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_INITIALIZATION]
            );

        $flagshipStub->expects($this->never())->method('logError');

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

        Flagship::start($envId, $apiKey, $config);

        $this->assertSame($config, Flagship::getConfig());
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());

        //Test Start Flagship failed with empty apiKey
        $envId = "envId";
        $apiKey = "";
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);
        Flagship::start($envId, $apiKey, $config);
        $this->assertSame($config, Flagship::getConfig());
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());

        //Test Start Flagship failed with empty apiKey
        $envId = "";
        $apiKey = "";
        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);
        Flagship::start($envId, $apiKey, $config);
        $this->assertSame($config, Flagship::getConfig());
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());
    }

    public function testStartFailedWithLog()
    {
        $envId = null;
        $apiKey = "apiKey";

        $config = new FlagshipConfig($envId, $apiKey);
        $logManager = new LogManager();
        $config->setLogManager($logManager);

        $flagshipStub = $this->getMockBuilder('Flagship\Flagship')
            ->setMethods(['logInfo','logError'])
            ->disableOriginalConstructor()->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipStub);

        $flagshipStub->expects($this->once())->method('logError')
            ->with(
                $config->getLogManager(),
                FlagshipConstant::INITIALIZATION_PARAM_ERROR,
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_INITIALIZATION]
            );
        $flagshipStub->expects($this->never())->method('logInfo');

        Flagship::start($envId, $apiKey, $config);
        $this->assertSame($config, Flagship::getConfig());
        $this->assertFalse(Flagship::isReady());
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());
    }

    public function testGetStatus()
    {
        //Test Status default is NO_READY
        $this->assertSame(FlagshipStatus::NOT_READY, Flagship::getStatus());

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
        $this->assertSame($context['age'], $visitor1->getContext()['age']);
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

    public function testNewVisitorThrowException()
    {
        $flagshipMock = $this->getMockBuilder('Flagship\Flagship')
            ->setMethods(['logError','getContainer'])
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->getMock();

        $instanceMethod = Utils::getMethod("Flagship\Flagship", 'getInstance');
        $instance = $instanceMethod->invoke(null);

        Utils::setPrivateProperty($instance, 'instance', $flagshipMock);

        $envId = "end_id";
        $apiKey = "apiKey";

        $config = new FlagshipConfig($envId, $apiKey);
        $config->setLogManager($this->logManagerMock);

        Flagship::start($envId, $apiKey, $config);

        $context = ['age' => 20];
        $visitorId = "visitorId";

        $exception = new Exception('throw Exception');

        $containerMock->method('get')->willThrowException($exception);
        $flagshipMock->expects($this->once())->method('getContainer')->willReturn($containerMock);

        $flagshipMock->expects($this->once())->method('logError')->with(
            $config->getLogManager(),
            $exception->getMessage(),
            [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_NEW_VISITOR]
        );

        $visitor = Flagship::newVisitor($visitorId, $context);
        $this->assertNull($visitor);
    }
}
