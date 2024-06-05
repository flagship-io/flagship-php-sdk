<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSSdkStatus;
use Flagship\Flag\FSFlagMetadata;
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
            ['info']
        );

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            [],
            '',
            false,
            false,
            true,
            ["setTroubleshootingData"]
        );

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setDisableDeveloperUsageTracking(true);
        $config->setLogManager($logManagerStub);

        $logMessageBuild = function ($functionName) {
            return [sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FSSdkStatus::getStatusName(FSSdkStatus::SDK_PANIC)
            ),
            [FlagshipConstant::TAG => $functionName]];
        };

        $logMessageBuildConsent = function ($functionName) {
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return [
                sprintf(
                    FlagshipConstant::METHOD_DEACTIVATED_SEND_CONSENT_ERROR,
                    FSSdkStatus::getStatusName(FSSdkStatus::SDK_PANIC)
                ),
                [FlagshipConstant::TAG => $functionName]];
        };



        $httpClientMock->expects($this->exactly(1))->method("post")
            ->willReturnOnConsecutiveCalls(
                new HttpResponse(500, [])
            );

        $configManager = (new ConfigManager())->setConfig($config);

        $decisionManager = new ApiManager($httpClientMock, $config);

        $configManager->setDecisionManager($decisionManager)->setTrackingManager($trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $logManagerStub->expects($this->exactly(8))->method('info')
            ->withConsecutive(
                $logMessageBuild('updateContext'),
                $logMessageBuild('updateContextCollection'),
                $logMessageBuild('clearContext'),
                $logMessageBuild('sendHit'),
                $logMessageBuildConsent('setConsent'),
                $logMessageBuild('getFlagValue'),
                $logMessageBuild('visitorExposed'),
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

        //Test sendHit
        $panicStrategy->sendHit(new Page('http://localhost'));

        //Test setConsent
        $panicStrategy->setConsent(true);
        $this->assertSame(true, $visitor->hasConsented());

        //Test getFlagValue
        $value = $panicStrategy->getFlagValue('key', true);
        $this->assertEquals(true, $value);

        //Test userExposed
        $panicStrategy->visitorExposed('key', true, null);

        //Test getFlagMetadata
        $panicStrategy->getFlagMetadata('key', null);

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
                StrategyAbstract::ACTIVATED => false,
                StrategyAbstract::FLAGS => $modifications[FlagshipField::FIELD_VALUE]
            ];
        }

        $visitorCache = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::VISITOR_ID => $visitor->getVisitorId(),
                StrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => $campaigns,
                StrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
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
