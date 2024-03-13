<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Hit\Page;
use Flagship\Model\FlagDTO;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;

class NoConsentStrategyTest extends TestCase
{
    use CampaignsData;

    public function testMethods()
    {
        $modifications = $this->campaignsModifications();

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

        $logMessageBuild = function ($functionName) use ($visitorId) {
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return [sprintf(
                FlagshipConstant::METHOD_DEACTIVATED_CONSENT_ERROR,
                $functionName,
                $visitorId
            ),
                [FlagshipConstant::TAG => $functionName]];
        };



        $modificationKey = $modifications[0]->getKey();
        $modificationValue = $modifications[0]->getValue();

        $httpClientMock->expects($this->exactly(2))->method("post")
            ->willReturnOnConsecutiveCalls(
                new HttpResponse(200, $this->campaigns()),
                new HttpResponse(500, null)
            );

        $configManager = (new ConfigManager())->setConfig($config);
        $decisionManager = new ApiManager($httpClientMock, $config);
        $configManager->setDecisionManager($decisionManager)
            ->setTrackingManager($trackerManager);

        $visitor = new VisitorDelegate(new Container(), $configManager, $visitorId, false, [], true);

        $logManagerStub->expects($this->exactly(4))->method('info')
            ->withConsecutive(
                $logMessageBuild('activateModification'),
                $logMessageBuild('activateModification'),
                $logMessageBuild('sendHit'),
                $logMessageBuild('visitorExposed')
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

        //Test getModification
        $defaultValue = 15;
        $valueOutput = $noConsentStrategy->getModification($modificationKey, $defaultValue, true);

        $this->assertSame($valueOutput, $modificationValue);

        //Test activateModification
        $noConsentStrategy->activateModification('key');

        //Test sendHit
        $noConsentStrategy->sendHit(new Page('http://localhost'));

        //Test userExposed
        $noConsentStrategy->visitorExposed('key', true, null);

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
