<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use PHPUnit\Framework\TestCase;

class ActivateBatchTest extends TestCase
{
    public function testToApiKeys()
    {

        $variationId = "varId";
        $variationGroupId = "varGrId";
        $envId = "envId";
        $visitorId = "visitorId";

        $config = new DecisionApiConfig($envId);

        $activate = new Activate($variationGroupId, $variationId);
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
