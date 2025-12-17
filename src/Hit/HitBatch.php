<?php

namespace Flagship\Hit;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class HitBatch extends HitAbstract
{
    /**
     * @var HitAbstract[]
     */
    protected array $hits;

    /**
     * @return HitAbstract[]
     */
    public function getHits(): array
    {
        return $this->hits;
    }

    /**
     * @param HitAbstract[] $hits
     */
    public function __construct(FlagshipConfig $config, array $hits)
    {
        parent::__construct(HitType::BATCH);
        $this->hits = $hits;
        $this->config = $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiKeys(): array
    {
        $data = [
                 FlagshipConstant::DS_API_ITEM              => $this->getDs(),
                 FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->getConfig()?->getEnvId(),
                 FlagshipConstant::T_API_ITEM               => $this->getType()->value,
                 FlagshipConstant::QT_API_ITEM              => $this->getNow() - $this->createdAt,
                 FlagshipConstant::H_API_ITEM               => [],
                ];

        foreach ($this->getHits() as $hit) {
            $hitApiKey = $hit->toApiKeys();
            unset($hitApiKey[FlagshipConstant::DS_API_ITEM]);
            unset($hitApiKey[FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM]);
            $data[FlagshipConstant::H_API_ITEM][] = $hitApiKey;
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return '';
    }
}
