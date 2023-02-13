<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FlagshipStatus;
use Flagship\Flag\FlagMetadata;
use Flagship\Hit\Page;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;

class PanicStrategyTest extends TestCase
{
    use CampaignsData;

    public function testMethods()
    {

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
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

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['sendConsentHit'],
            '',
            false
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $logMessageBuild = function ($functionName) {
            return [sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FlagshipStatus::getStatusName(FlagshipStatus::READY_PANIC_ON)
            ),
            [FlagshipConstant::TAG => $functionName]];
        };

        $logMessageBuildConsent = function ($functionName) {
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return [
                sprintf(
                    FlagshipConstant::METHOD_DEACTIVATED_SEND_CONSENT_ERROR,
                    FlagshipStatus::getStatusName(FlagshipStatus::READY_PANIC_ON)
                ),
                [FlagshipConstant::TAG => $functionName]];
        };



        $httpClientMock->expects($this->exactly(2))->method("post")
            ->willReturnOnConsecutiveCalls(
                new HttpResponse(200, $this->campaigns()),
                new HttpResponse(500, [])
            );

        $configManager = (new ConfigManager())->setConfig($config);

        $decisionManager = new ApiManager($httpClientMock, $config);

        $configManager->setDecisionManager($decisionManager)->setTrackingManager($trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $logManagerStub->expects($this->exactly(11))->method('error')
            ->withConsecutive(
                $logMessageBuild('updateContext'),
                $logMessageBuild('updateContextCollection'),
                $logMessageBuild('clearContext'),
                $logMessageBuild('getModification'),
                $logMessageBuild('getModificationInfo'),
                $logMessageBuild('activateModification'),
                $logMessageBuild('sendHit'),
                $logMessageBuildConsent('setConsent'),
                $logMessageBuild('getFlagValue'),
                $logMessageBuild('userExposed'),
                $logMessageBuild('getFlagMetadata')
            );

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
        $panicStrategy->synchronizeModifications();

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

        //Test setConsent
        $panicStrategy->setConsent(true);
        $this->assertSame(true, $visitor->hasConsented());

        //Test getFlagValue
        $value = $panicStrategy->getFlagValue('key', true);
        $this->assertEquals(true, $value);

        //Test userExposed
        $panicStrategy->userExposed('key', true, null);

        //Test getFlagMetadata
        $panicStrategy->getFlagMetadata('key', FlagMetadata::getEmpty(), true);

        $campaignsData = $this->campaigns();
        $assignmentsHistory = [];
        $campaigns = [];
        foreach ($campaignsData[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory[$campaign[FlagshipField::FIELD_ID]] = $variation[FlagshipField::FIELD_ID];

            $campaigns[] = [
                FlagshipField::FIELD_CAMPAIGN_ID => $campaign[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                FlagshipField::FIELD_VARIATION_ID => $variation[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_IS_REFERENCE => $variation[FlagshipField::FIELD_REFERENCE],
                FlagshipField::FIELD_CAMPAIGN_TYPE => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                VisitorStrategyAbstract::ACTIVATED => false,
                VisitorStrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }

        $visitorCache = [
            VisitorStrategyAbstract::VERSION => 1,
            VisitorStrategyAbstract::DATA => [
                VisitorStrategyAbstract::VISITOR_ID => $visitor->getVisitorId(),
                VisitorStrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                VisitorStrategyAbstract::CONSENT => $visitor->hasConsented(),
                VisitorStrategyAbstract::CONTEXT => $visitor->getContext(),
                VisitorStrategyAbstract::CAMPAIGNS => $campaigns,
                VisitorStrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
            ]
        ];

        //Test fetchVisitorCampaigns
        $visitor->visitorCache = $visitorCache;
        $panicStrategy->fetchFlags();

        $this->assertCount(0, $visitor->getFlagsDTO());

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
        $panicStrategy->lookupVisitor();

        // test cacheVisitor
        $panicStrategy->cacheVisitor();
    }
}
