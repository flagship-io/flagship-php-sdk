<?php

namespace Flagship\Hit;

use Flagship\Enum\HitType;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\DecisionApiConfig;

class SegmentTest extends TestCase
{
     use PHPMock;
    public function testConstructor()
    {
        $round = $this->getFunctionMock("Flagship\Traits", 'round');
        $round->expects($this->any())->willReturn(0);

        $envId = "envId";
        $visitorId = "visitorId";

        $context = [
            "key1" => "value1",
            "key2" => 1,
            "key3" => true,
            "key4" => null,
            "key5" => false,
            "key6" => ["subKey" => "subValue"]
        ];

        $config = new DecisionApiConfig($envId);



        $segment = new Segment($context, $config);
        $segment->setConfig($config)->setVisitorId($visitorId);

        $segment->setSl(["key"]);

        $this->assertSame($context, $segment->getSl());

        $segmentArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM      => $visitorId,
            FlagshipConstant::DS_API_ITEM              => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM               => HitType::SEGMENT->value,
            FlagshipConstant::CUSTOMER_UID             => null,
            FlagshipConstant::QT_API_ITEM              => 0.0,
            FlagshipConstant::SL_API_ITEM              => [
                "key1" => "value1",
                "key2" => "1",
                "key3" => "true",
                "key4" => "null",
                "key5" => "false",
                "key6" => ''
            ]
        ];

        $this->assertSame($segmentArray, $segment->toApiKeys());

        $this->assertTrue($segment->isReady());

        $this->assertSame(Segment::ERROR_MESSAGE, $segment->getErrorMessage());

        $segment1 = new Segment([], $config);

        $segment1->setConfig($config)->setVisitorId($visitorId);

        $segmentArray[FlagshipConstant::SL_API_ITEM] = [];

        $this->assertSame($segmentArray, $segment1->toApiKeys());

        $this->assertFalse($segment1->isReady());
    }
}
