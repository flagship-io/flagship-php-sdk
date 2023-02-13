<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use PHPUnit\Framework\TestCase;

class SegmentTest extends TestCase
{
    public function testConstructor()
    {
        $envId = "envId";
        $visitorId = "visitorId";

        $context = [
            "key1"=>"value1",
            "key2"=>1,
            "key3"=>true
        ];

        $config = new DecisionApiConfig($envId);

        $segment = new Segment($context);
        $segment->setConfig($config)->setVisitorId($visitorId);

        $segment->setSl(["key"]);

        $this->assertSame($context, $segment->getSl());

        $segmentArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::SEGMENT,
            FlagshipConstant::USER_IP_API_ITEM => null,
            FlagshipConstant::SCREEN_RESOLUTION_API_ITEM => null,
            FlagshipConstant::USER_LANGUAGE => null,
            FlagshipConstant::SESSION_NUMBER => null,
            FlagshipConstant::CUSTOMER_UID => null,
            FlagshipConstant::SL_API_ITEM => $context
        ];

        $this->assertSame($segmentArray, $segment->toApiKeys());

        $this->assertTrue($segment->isReady());

        $this->assertSame(Segment::ERROR_MESSAGE, $segment->getErrorMessage());

    }
}
