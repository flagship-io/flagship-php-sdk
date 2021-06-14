<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Hit\Page;
use Flagship\Model\Modification;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;

class NoConsentStrategyTest extends TestCase
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

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $visitorId = "visitorId";

        $logMessageBuild = function ($functionName) use ($visitorId) {
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return ["[$flagshipSdk] " . sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_CONSENT_ERROR,
                $functionName,
                $visitorId
            ),
                [FlagshipConstant::TAG => $functionName]];
        };

        $logManagerStub->expects($this->exactly(3))->method('error')
            ->withConsecutive(
                $logMessageBuild('activateModification'),
                $logMessageBuild('activateModification'),
                $logMessageBuild('sendHit')
            );

        $modificationKey = "age";
        $modificationValue = 20;
        $apiManagerStub->expects($this->once())
            ->method('getCampaignModifications')
            ->willReturn([(new Modification())->setKey($modificationKey)->setValue($modificationValue)]);

        $configManager = (new ConfigManager())->setConfig($config);
        $configManager->setDecisionManager($apiManagerStub);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, []);

        $noConsentStrategy = new NoConsentStrategy($visitor);

        //Test updateContext
        $key = "key";
        $value = "value";
        $noConsentStrategy->updateContext($key, $value);

        $this->assertSame([
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
            $key => $value,
            ], $visitor->getContext());

        //Test updateContextCollection
        $noConsentStrategy->updateContextCollection(['age' => 20]);

        $this->assertSame([
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
            $key => $value, 'age' => 20], $visitor->getContext());

        //Test clearContext
        $noConsentStrategy->clearContext();

        $this->assertCount(0, $visitor->getContext());

        //Test synchronizedModifications
        $noConsentStrategy->synchronizedModifications();

        //Test getModification
        $defaultValue = 15;
        $valueOutput = $noConsentStrategy->getModification($modificationKey, $defaultValue, true);

        $this->assertSame($valueOutput, $modificationValue);

        //Test activateModification
        $noConsentStrategy->activateModification('key');

        //Test sendHit
        $noConsentStrategy->sendHit(new Page('http://localhost'));
    }
}
