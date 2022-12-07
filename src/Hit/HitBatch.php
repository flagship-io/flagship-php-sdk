<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;

class HitBatch extends HitAbstract
{
    /**
     * @var HitAbstract[]
     */
    protected $hits;

    /**
     * @return HitAbstract[]
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param HitAbstract[] $hits
     */
    public function __construct(array $hits)
    {
        parent::__construct("BATCH");
        $this->hits=$hits;
    }

    public function toArray()
    {
        $data = [
            FlagshipConstant::DS_API_ITEM => $this->getDs(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->getConfig()->getEnvId(),
            FlagshipConstant::T_API_ITEM => $this->getType(),
            FlagshipConstant::QT_API_ITEM => 	round(microtime(true) * 1000) - $this->createdAt,
            FlagshipConstant::H_API_ITEM => []
        ];

        foreach ($this->hits as $hit) {
            $hitApiKey = $hit->toArray();
            unset($hitApiKey[FlagshipConstant::DS_API_ITEM]);
            unset($hitApiKey[FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM]);
            $data[FlagshipConstant::H_API_ITEM][] = $hitApiKey;
      }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        // TODO: Implement getErrorMessage() method.
    }
}