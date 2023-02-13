<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use PHPUnit\Framework\TestCase;

class ActivateTest extends TestCase
{
    public function testTestConstruct()
    {
        $variationId = "varId";
        $variationGroupId = "varGrId";
        $envId = "envId";
        $visitorId = "visitorId";

        $config = new DecisionApiConfig($envId);

        $activate = new Activate($variationGroupId, $variationId);
        $activate->setConfig($config)->setDs(FlagshipConstant::SDK_APP)->setVisitorId($visitorId);

        $this->assertSame($variationId, $activate->getVariationId());
        $this->assertSame($variationGroupId, $activate->getVariationGroupId());

        $variationId = "varId2";
        $variationGroupId = "varGrId2";

        $activate->setVariationId($variationId)->setVariationGroupId($variationGroupId);

        $this->assertSame($variationId, $activate->getVariationId());
        $this->assertSame($variationGroupId, $activate->getVariationGroupId());

        $apiKeys = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::VARIATION_ID_API_ITEM => $variationId,
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $variationGroupId,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::ANONYMOUS_ID => null
        ];

        $this->assertSame($apiKeys, $activate->toApiKeys());

        $anonymousId = "anonymousId";
        $activate->setAnonymousId($anonymousId);

        $apiKeys = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::VARIATION_ID_API_ITEM => $variationId,
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $variationGroupId,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::ANONYMOUS_ID => $anonymousId
        ];

        $this->assertSame($apiKeys, $activate->toApiKeys());

        $this->assertTrue($activate->isReady());

        $this->assertSame(Activate::ERROR_MESSAGE, $activate->getErrorMessage());

    }
}
