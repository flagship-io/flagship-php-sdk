<?php

namespace Flagship\Visitor;

use Flagship\Enum\FSFetchReason;
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
        $containerGetMethod = function () {
            $args = func_get_args();
            $params = $args[1];
            switch ($args[0]) {
                case 'Flagship\Visitor\NotReadyStrategy':
                    $returnValue = new NotReadyStrategy($params[0]);
                    break;
                case 'Flagship\Visitor\VisitorDelegate':
                    $returnValue = new VisitorDelegate(
                        $params[0],
                        $params[1],
                        $params[2],
                        $params[3],
                        $params [4],
                        $params[5]
                    );
                    break;
                case 'Flagship\Visitor\Visitor':
                    $returnValue = new Visitor($args[1][0]);
                    break;
                default:
                    $returnValue = null;
            }
            return $returnValue;
        };

        $trackerManager = $this->getMockBuilder('Flagship\Api\TrackingManager')
            ->setMethods(['addHit'])
            ->disableOriginalConstructor()->getMock();

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->setMethods(['get'])->disableOriginalConstructor()->getMock();

        $containerMock->method('get')
            ->will($this->returnCallback($containerGetMethod));

        $visitorId = "visitorId";
        $hasConsented = true;
        $configManager = new ConfigManager();
        $config = new DecisionApiConfig();

        $configManager->setConfig($config);
        $configManager->setTrackingManager($trackerManager);

        $visitor = VisitorBuilder::builder($visitorId, $hasConsented, $configManager, $containerMock, null)->build();

        $this->assertEquals($visitorId, $visitor->getVisitorId());
        $this->assertTrue($visitor->hasConsented());
        $this->assertNull($visitor->getAnonymousId());

        $context = [
            'age' => 20,
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
        ];

        $onFetchFlagsStatusChanged = function (FetchFlagsStatusInterface $fetchFlagsStatus) {
            $this->assertSame($fetchFlagsStatus->getStatus(), FSFetchStatus::FETCH_REQUIRED);
            $this->assertSame($fetchFlagsStatus->getReason(), FSFetchReason::VISITOR_CREATED);
        };

        $visitor = VisitorBuilder::builder($visitorId, $hasConsented, $configManager, $containerMock, null)
            ->isAuthenticated(true)
            ->onFetchFlagsStatusChanged($onFetchFlagsStatusChanged)
            ->withContext($context)->build();

        $this->assertSame($context, $visitor->getContext());
        $this->assertTrue($visitor->hasConsented());
        $this->assertNotNull($visitor->getAnonymousId());
        
    }
}
