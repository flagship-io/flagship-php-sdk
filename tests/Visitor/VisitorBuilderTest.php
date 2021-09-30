<?php

namespace Flagship\Visitor;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Utils\ConfigManager;
use PHPUnit\Framework\TestCase;

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
            ->setMethods(['sendConsentHit'])
            ->disableOriginalConstructor()->getMock();

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->setMethods(['get'])->disableOriginalConstructor()->getMock();

        $containerMock->method('get')
            ->will($this->returnCallback($containerGetMethod));

        $visitorId = "visitorId";
        $configManager = new ConfigManager();
        $config = new DecisionApiConfig();
        $configManager->setConfig($config);
        $configManager->setTrackingManager($trackerManager);

        $visitor = VisitorBuilder::builder($visitorId, $configManager, $containerMock)->build();

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

        $visitor = VisitorBuilder::builder($visitorId, $configManager, $containerMock)
            ->isAuthenticated(true)
            ->hasConsented(true)
            ->context($context)->build();

        $this->assertSame($context, $visitor->getContext());
        $this->assertTrue($visitor->hasConsented());
        $this->assertNotNull($visitor->getAnonymousId());
    }
}
