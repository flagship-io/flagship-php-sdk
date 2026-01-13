<?php

namespace Flagship\Visitor;

use Flagship\Hit\Page;
use Flagship\Utils\Container;
use Flagship\Enum\FSSdkStatus;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;
use Flagship\Model\HttpResponse;
use Flagship\Decision\ApiManager;
use Flagship\Utils\ConfigManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\VisitorCacheDTO;
use Flagship\Config\DecisionApiConfig;

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
            return sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_ERROR,
                $functionName,
                FSSdkStatus::SDK_PANIC->name
            );
        };

        $logMessageBuildConsent = function () {
            return
                sprintf(
                    FlagshipConstant::METHOD_DEACTIVATED_SEND_CONSENT_ERROR,
                    FSSdkStatus::SDK_PANIC->name,
                );
        };



        $httpClientMock->expects($this->exactly(1))->method("post")->willReturnOnConsecutiveCalls(
            new HttpResponse(500, [])
        );

        $decisionManager = new ApiManager($httpClientMock, $config);

        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, "visitorId", false, [], true);

        $logManagerStub->expects($this->exactly(8))->method('info')->with(
            $this->logicalOr(
                $logMessageBuild('updateContext'),
                $logMessageBuild('updateContextCollection'),
                $logMessageBuild('clearContext'),
                $logMessageBuild('sendHit'),
                $logMessageBuildConsent(),
                $logMessageBuild('getFlagValue'),
                $logMessageBuild('visitorExposed'),
                $logMessageBuild('getFlagMetadata')
            ),
            $this->logicalOr(
                [FlagshipConstant::TAG => 'updateContext'],
                [FlagshipConstant::TAG => 'updateContextCollection'],
                [FlagshipConstant::TAG => 'clearContext'],
                [FlagshipConstant::TAG => 'sendHit'],
                [FlagshipConstant::TAG => 'setConsent'],
                [FlagshipConstant::TAG => 'getFlagValue'],
                [FlagshipConstant::TAG => 'visitorExposed'],
                [FlagshipConstant::TAG => 'getFlagMetadata']
            )
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
        $this->assertTrue($value);

        //Test userExposed
        $panicStrategy->visitorExposed('key', true);

        //Test getFlagMetadata
        $panicStrategy->getFlagMetadata('key');

        $campaignsData = $this->campaigns();
        $assignmentsHistory = [];
        $campaigns = [];
        foreach ($campaignsData[FlagshipField::FIELD_CAMPAIGNS] as $campaign) {
            $variation = $campaign[FlagshipField::FIELD_VARIATION];
            $modifications = $variation[FlagshipField::FIELD_MODIFICATIONS];
            $assignmentsHistory[$campaign[FlagshipField::FIELD_ID]] = $variation[FlagshipField::FIELD_ID];

            $campaigns[] = [
                FlagshipField::FIELD_CAMPAIGN_ID        => $campaign[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_VARIATION_GROUP_ID => $campaign[FlagshipField::FIELD_VARIATION_GROUP_ID],
                FlagshipField::FIELD_VARIATION_ID       => $variation[FlagshipField::FIELD_ID],
                FlagshipField::FIELD_IS_REFERENCE       => $variation[FlagshipField::FIELD_REFERENCE],
                FlagshipField::FIELD_CAMPAIGN_TYPE      => $modifications[FlagshipField::FIELD_CAMPAIGN_TYPE],
                StrategyAbstract::ACTIVATED             => false,
                StrategyAbstract::FLAGS                 => $modifications,
            ];
        }

        $visitorCache = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA    => [
                StrategyAbstract::VISITOR_ID          => $visitor->getVisitorId(),
                StrategyAbstract::ANONYMOUS_ID        => $visitor->getAnonymousId(),
                StrategyAbstract::CONSENT             => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT             => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS           => $campaigns,
                StrategyAbstract::ASSIGNMENTS_HISTORY => $assignmentsHistory,
            ],
        ];

        $visitorCache = VisitorCacheDTO::fromArray($visitorCache);

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
            [
                'lookupVisitor',
                'cacheVisitor',
            ]
        );

        $VisitorCacheImplementationMock->expects($this->never())->method("cacheVisitor");

        $VisitorCacheImplementationMock->expects($this->never())->method("lookupVisitor");

        $config->setVisitorCacheImplementation($VisitorCacheImplementationMock);

        // test lookupVisitor
        $panicStrategy->lookupVisitor();

        // test cacheVisitor
        $panicStrategy->cacheVisitor();
    }
}
