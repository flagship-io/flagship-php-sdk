<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\FlagshipConfig;
use Flagship\Hit\Page;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;

class PanicStrategyTest extends TestCase
{
    public function testMethods()
    {
        $apiManagerStub = $this->getMockForAbstractClass(
            'Flagship\Decision\DecisionManagerAbstract',
            [],
            'ApiManagerInterface',
            false,
            true,
            true,
            ['getCampaignModifications', 'getConfig']
        );

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $config = new FlagshipConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $logMessageBuild = function ($functionName) {
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return ["[$flagshipSdk] " . sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FlagshipStatus::READY_PANIC_ON
            ),
            [FlagshipConstant::TAG => $functionName]];
        };

        $logManagerStub->expects($this->exactly(7))->method('error')
            ->withConsecutive(
                $logMessageBuild('updateContext'),
                $logMessageBuild('updateContextCollection'),
                $logMessageBuild('clearContext'),
                $logMessageBuild('getModification'),
                $logMessageBuild('getModificationInfo'),
                $logMessageBuild('activateModification'),
                $logMessageBuild('sendHit')
            );

        $apiManagerStub->expects($this->once())->method('getCampaignModifications');

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);

        $panicStrategy = new PanicStrategy($visitor);

        //Test updateContext
        $key = "key";
        $value = "value";
        $panicStrategy->updateContext($key, $value);

        //Test updateContextCollection
        $panicStrategy->updateContextCollection([]);

        //Test clearContext
        $panicStrategy->clearContext();

        //Test synchronizedModifications
        $panicStrategy->synchronizedModifications();

        //Test getModification
        $defaultValue = "defaultValue";
        $valueOutput = $panicStrategy->getModification('key', $defaultValue);

        $this->assertSame($valueOutput, $defaultValue);

        //Test getModificationInfo
        $valueOutput = $panicStrategy->getModificationInfo('key');
        $this->assertNull($valueOutput);

        //Test activateModification
        $panicStrategy->activateModification('key');

        //Test sendHit
        $panicStrategy->sendHit(new Page('http://localhost'));
    }
}
