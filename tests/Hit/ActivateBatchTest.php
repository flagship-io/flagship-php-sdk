<?php

namespace Flagship\Hit;

use Flagship\BaseTestCase;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Flag\FSFlagMetadata;

class ActivateBatchTest extends BaseTestCase
{
    public function testToApiKeys()
    {
        $this->mockRoundFunction();

        $variationId = "varId";
        $variationGroupId = "varGrId";
        $envId = "envId";
        $visitorId = "visitorId";

        $config = new DecisionApiConfig($envId);

        $activate = new Activate($variationGroupId, $variationId, "key", new FSFlagMetadata(
            "campaignId",
            $variationGroupId,
            $variationId,
            true,
            "campaignType",
            "slug",
            "campaignName",
            "variationGroupName",
            "variationName"
        ));

        $activate->setConfig($config)->setVisitorId($visitorId);

        $activateBatch = new ActivateBatch($config, [$activate]);

        $apiKeys = $activate->toApiKeys();
        unset($apiKeys[FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM]);

        $this->assertSame([
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
            FlagshipConstant::BATCH                    => [$apiKeys],
        ], $activateBatch->toApiKeys());
    }
}
