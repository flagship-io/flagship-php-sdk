<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FSSdkStatus;
use Flagship\Hit\Page;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;

class NotReadyStrategyTest extends TestCase
{
    public function testMethods()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $logMessageBuild = function ($functionName) {
            return sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FSSdkStatus::SDK_NOT_INITIALIZED->name,
            );
        };


        $decisionManager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitorId = "visitorId";
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $logManagerStub->expects($this->exactly(5))->method('error')
            ->with(
                $this->logicalOr(
                    $logMessageBuild('sendHit'),
                    $logMessageBuild('fetchFlags'),
                    $logMessageBuild('getFlagValue'),
                    $logMessageBuild('visitorExposed'),
                    $logMessageBuild('getFlagMetadata')
                ),
                $this->logicalOr(
                    [FlagshipConstant::TAG => 'sendHit'],
                    [FlagshipConstant::TAG => 'fetchFlags'],
                    [FlagshipConstant::TAG => 'getFlagValue'],
                    [FlagshipConstant::TAG => 'visitorExposed'],
                    [FlagshipConstant::TAG => 'getFlagMetadata']
                )
            );

        $notReadyStrategy = new NotReadyStrategy($visitor);

        //Test updateContext
        $key = "key";
        $value = "value";
        $notReadyStrategy->updateContext($key, $value);


        $this->assertSame([

            "sdk_osName" => PHP_OS,
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
            $key => $value,
            ], $visitor->getContext());

        //Test updateContextCollection
        $notReadyStrategy->updateContextCollection(['age' => 20]);

        $this->assertSame([
            "sdk_osName" => PHP_OS,
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
            $key => $value,
            'age' => 20
        ], $visitor->getContext());

        //Test clearContext
        $notReadyStrategy->clearContext();

        $this->assertCount(0, $visitor->getContext());

        //Test sendHit
        $notReadyStrategy->sendHit(new Page('http://localhost'));

        //Test fetchFlags
        $notReadyStrategy->fetchFlags();

        //Test getFlagValue
        $value = $notReadyStrategy->getFlagValue('key', true);

        $this->assertTrue($value);

        //Test userExposed
        $notReadyStrategy->visitorExposed('key', true);

        //Test getFlagMetadata
        $notReadyStrategy->getFlagMetadata('key');

        $VisitorCacheImplementationMock = $this->getMockForAbstractClass(
            "Flagship\Cache\IVisitorCacheImplementation",
            [],
            "",
            true,
            true,
            true,
            ['lookupVisitor', 'cacheVisitor']
        );

        $VisitorCacheImplementationMock->expects($this->never())
            ->method("cacheVisitor");

        $VisitorCacheImplementationMock->expects($this->never())
            ->method("lookupVisitor");

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);

        // test lookupVisitor
        $notReadyStrategy->lookupVisitor();

        // test cacheVisitor
        $notReadyStrategy->cacheVisitor();
    }
}
