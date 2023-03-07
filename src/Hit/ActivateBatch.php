<?php

namespace Flagship\Hit;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;

class ActivateBatch
{
    /***
     * @var Activate[]
     */
    protected $hits;

    /**
     * @var FlagshipConfig
     */
    protected $config;

    /**
     * @param FlagshipConfig $config
     * @param array $hits
     */
    public function __construct(FlagshipConfig $config, array $hits)
    {
        $this->config = $config;
        $this->hits = $hits;
    }

    /**
     * @return array
     */
    public function toApiKeys()
    {
        $activates = [];
        foreach ($this->hits as $hit) {
            $apiKeys = $hit->toApiKeys();
            unset($apiKeys[FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM]);
            $activates[] = $apiKeys;
        }
        return [
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->config->getEnvId(),
            FlagshipConstant::BATCH => $activates
        ];
    }
}
