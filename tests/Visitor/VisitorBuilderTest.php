<?php

declare(strict_types=1);

namespace Flagship\Visitor;

use Flagship\Enum\FSFetchReason;
use Flagship\Utils\Container;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FSFetchStatus;
use Flagship\Utils\ConfigManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\DecisionApiConfig;
use Flagship\Model\FetchFlagsStatusInterface;

class VisitorBuilderTest extends TestCase
{
    public function testBuilder()
    {

        $trackerManager = $this->getMockBuilder('Flagship\Api\TrackingManager')->onlyMethods(['addHit'])->disableOriginalConstructor()->getMock();

        $container = new Container();

        $visitorId = "visitorId";
        $hasConsented = true;

        $config = new DecisionApiConfig();
        $decisionManager = $this->getMockBuilder('Flagship\Decision\ApiManager')->disableOriginalConstructor()->getMock();
        $configManager = new ConfigManager($config, $decisionManager, $trackerManager);

        $visitor = VisitorBuilder::builder(
            $visitorId,
            $hasConsented,
            $configManager,
            $container,
            "instanceId"
        )->build();

        $this->assertEquals($visitorId, $visitor->getVisitorId());
        $this->assertTrue($visitor->hasConsented());
        $this->assertNull($visitor->getAnonymousId());

        $context = [
                    'age'                        => 20,
                    "sdk_osName"                 => PHP_OS,
                    "sdk_deviceType"             => "server",
                    FlagshipConstant::FS_CLIENT  => FlagshipConstant::SDK_LANGUAGE,
                    FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
                    FlagshipConstant::FS_USERS   => $visitorId,
                   ];

        $onFetchFlagsStatusChanged = function (FetchFlagsStatusInterface $fetchFlagsStatus) {
            $this->assertSame($fetchFlagsStatus->getStatus(), FSFetchStatus::FETCH_REQUIRED);
            $this->assertSame($fetchFlagsStatus->getReason(), FSFetchReason::VISITOR_CREATED);
        };

        $visitor = VisitorBuilder::builder($visitorId, $hasConsented, $configManager, $container, "instanceId")->setIsAuthenticated(true)->setOnFetchFlagsStatusChanged($onFetchFlagsStatusChanged)->setContext($context)->build();

        $this->assertSame($context, $visitor->getContext());
        $this->assertTrue($visitor->hasConsented());
        $this->assertNotNull($visitor->getAnonymousId());
    }
}
