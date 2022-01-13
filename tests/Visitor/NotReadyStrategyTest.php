<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\Flag\FlagMetadata;
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
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return ["[$flagshipSdk] " . sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FlagshipStatus::NOT_INITIALIZED
            ),
                [FlagshipConstant::TAG => $functionName]];
        };

        $logManagerStub->expects($this->exactly(9))->method('error')
            ->withConsecutive(
                $logMessageBuild('synchronizeModifications'),
                $logMessageBuild('getModification'),
                $logMessageBuild('getModificationInfo'),
                $logMessageBuild('activateModification'),
                $logMessageBuild('sendHit'),
                $logMessageBuild('fetchFlags'),
                $logMessageBuild('getFlagValue'),
                $logMessageBuild('userExposed'),
                $logMessageBuild('getFlagMetadata')
            );

        $configManager = (new ConfigManager())->setConfig($config)->setTrackingManager($trackerManager);
        $visitorId = "visitorId";
        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

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

        //Test synchronizedModifications
        $notReadyStrategy->synchronizeModifications();

        //Test getModification
        $defaultValue = "defaultValue";
        $valueOutput = $notReadyStrategy->getModification('key', $defaultValue);

        $this->assertSame($defaultValue, $valueOutput);

        //Test getModificationInfo
        $valueOutput = $notReadyStrategy->getModificationInfo('key');
        $this->assertNull($valueOutput);

        //Test activateModification
        $notReadyStrategy->activateModification('key');

        //Test sendHit
        $notReadyStrategy->sendHit(new Page('http://localhost'));

        //Test fetchFlags
        $notReadyStrategy->fetchFlags();

        //Test getFlagValue
        $value = $notReadyStrategy->getFlagValue('key', true);
        $this->assertEquals(true, $value);

        //Test userExposed
        $notReadyStrategy->userExposed('key', true, null);

        //Test getFlagMetadata
        $notReadyStrategy->getFlagMetadata('key', FlagMetadata::getEmpty(), true);
    }
}
