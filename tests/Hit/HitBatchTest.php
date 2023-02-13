<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use PHPUnit\Framework\TestCase;

class HitBatchTest extends TestCase
{
    public function testToApiKeys()
    {
        $visitorId = "visitorId";
        $config = new DecisionApiConfig();

        $page = new Page("http://localhost");
        $page->setConfig($config)->setVisitorId($visitorId);

        $screen = new Screen("home");
        $screen->setConfig($config)->setVisitorId($visitorId);

        $hits = [$page, $screen];

        $batch = new HitBatch($config, [$page, $screen]);

        $data = [
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
            FlagshipConstant::T_API_ITEM => "BATCH",
            FlagshipConstant::QT_API_ITEM => 0,
            FlagshipConstant::H_API_ITEM => []
        ];

        foreach ($hits as $hit) {
            $hitApiKey = $hit->toApiKeys();
            unset($hitApiKey[FlagshipConstant::DS_API_ITEM]);
            unset($hitApiKey[FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM]);
            $data[FlagshipConstant::H_API_ITEM][] = $hitApiKey;
        }

        $this->assertSame($data, $batch->toApiKeys());

        $batch->getErrorMessage();
    }

}
