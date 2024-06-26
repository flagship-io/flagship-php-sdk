<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Hit\Page;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;

class NoConsentStrategyTest extends TestCase
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

        $visitorId = "visitorId";

        $httpClientMock->expects($this->exactly(2))->method("post")
            ->willReturnOnConsecutiveCalls(
                new HttpResponse(200, $this->campaigns()),
                new HttpResponse(500, null)
            );

        $decisionManager = new ApiManager($httpClientMock, $config);
        $configManager = (new ConfigManager($config, $decisionManager, $trackerManager));

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $logManagerStub->expects($this->exactly(2))->method('info')
            ->with(
                $this->logicalOr(
                    sprintf(
                        FlagshipConstant::METHOD_DEACTIVATED_CONSENT_ERROR,
                        "sendHit",
                        $visitorId
                    ),
                    sprintf(
                        FlagshipConstant::METHOD_DEACTIVATED_CONSENT_ERROR,
                        "visitorExposed",
                        $visitorId
                    )
                ),
                $this->logicalOr(
                    [FlagshipConstant::TAG => "sendHit"],
                    [FlagshipConstant::TAG => "visitorExposed"]
                )
            );

        $noConsentStrategy = new NoConsentStrategy($visitor);

        //Test updateContext
        $key = "key";
        $value = "value";
        $noConsentStrategy->updateContext($key, $value);

        $this->assertSame([
            "sdk_osName" => PHP_OS,
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
            $key => $value,
            ], $visitor->getContext());

        //Test updateContextCollection
        $noConsentStrategy->updateContextCollection(['age' => 20]);

        $this->assertSame([
            "sdk_osName" => PHP_OS,
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
            $key => $value, 'age' => 20], $visitor->getContext());

        //Test clearContext
        $noConsentStrategy->clearContext();

        $this->assertCount(0, $visitor->getContext());

        //Test synchronizedModifications
        $noConsentStrategy->fetchFlags();


        //Test sendHit
        $noConsentStrategy->sendHit(new Page('http://localhost'));

        //Test userExposed
        $noConsentStrategy->visitorExposed('key', true);

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
                StrategyAbstract::VISITOR_ID => $visitorId,
                StrategyAbstract::ANONYMOUS_ID => $visitor->getAnonymousId(),
                StrategyAbstract::CONSENT => $visitor->hasConsented(),
                StrategyAbstract::CONTEXT => $visitor->getContext(),
                StrategyAbstract::CAMPAIGNS => $campaigns,
                StrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
            ]
        ];

        //Test fetchVisitorCampaigns
        $visitor->visitorCache = $visitorCache;
        $noConsentStrategy->fetchFlags();

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
        $noConsentStrategy->lookupVisitor();

        // test cacheVisitor
        $noConsentStrategy->cacheVisitor();
    }
}
