<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipStatus;
use Flagship\FlagshipConfig;
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

        $config = new FlagshipConfig('envId', 'apiKey');
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

        $logManagerStub->expects($this->exactly(8))->method('error')
            ->withConsecutive(
                $logMessageBuild('updateContext'),
                $logMessageBuild('updateContextCollection'),
                $logMessageBuild('clearContext'),
                $logMessageBuild('synchronizedModifications'),
                $logMessageBuild('getModification'),
                $logMessageBuild('getModificationInfo'),
                $logMessageBuild('activateModification'),
                $logMessageBuild('sendHit')
            );


        $configManager = (new ConfigManager())->setConfig($config);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", []);

        $notReadyStrategy = new NotReadyStrategy($visitor);

        //Test updateContext
        $key = "key";
        $value = "value";
        $notReadyStrategy->updateContext($key, $value);

        //Test updateContextCollection
        $notReadyStrategy->updateContextCollection([]);

        //Test clearContext
        $notReadyStrategy->clearContext();

        //Test synchronizedModifications
        $notReadyStrategy->synchronizedModifications();

        //Test getModification
        $defaultValue = "defaultValue";
        $valueOutput = $notReadyStrategy->getModification('key', $defaultValue);

        $this->assertSame($valueOutput, $defaultValue);

        //Test getModificationInfo
        $valueOutput = $notReadyStrategy->getModificationInfo('key');
        $this->assertNull($valueOutput);

        //Test activateModification
        $notReadyStrategy->activateModification('key');

        //Test sendHit
        $notReadyStrategy->sendHit(new Page('http://localhost'));
    }
}
