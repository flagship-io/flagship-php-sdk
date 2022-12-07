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

    public function __construct(FlagshipConfig $config, array $hits)
    {
        $this->config = $config;
        $this->hits = $hits;
    }

    public function toArray(){
        $activates = [];
        foreach ($this->hits as $hit) {
            $apiKeys = $hit->toArray();
            unset($apiKeys[FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM]);
            $activates[] = $apiKeys;
        }
        return [
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->config->getEnvId(),
            FlagshipConstant::BATCH =>$activates
        ];
    }
}