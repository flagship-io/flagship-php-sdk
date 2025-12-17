<?php

namespace Flagship\Model;

use Flagship\Visitor\StrategyAbstract;

/**
 * @phpstan-import-type VisitorCacheArray from Types
 * @phpstan-import-type VisitorCacheDataArray from Types
 * @phpstan-import-type CampaignCacheArray from Types
 */
class VisitorCacheDTO
{
    private int $version;

    private VisitorCacheDataDTO $data;

    public function __construct(int $version, VisitorCacheDataDTO $data)
    {
        $this->version = $version;
        $this->data = $data;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getData(): VisitorCacheDataDTO
    {
        return $this->data;
    }

    public function setData(VisitorCacheDataDTO $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $version = $data[StrategyAbstract::VERSION] ?? StrategyAbstract::CURRENT_VERSION;
        $dataArray = $data[StrategyAbstract::DATA] ?? [];

        return new self(
            is_int($version) ? $version : StrategyAbstract::CURRENT_VERSION,
            is_array($dataArray) ? VisitorCacheDataDTO::fromArray($dataArray) : new VisitorCacheDataDTO('', null)
        );
    }

    /**
     * @return VisitorCacheArray
     */
    public function toArray(): array
    {
        return [
            StrategyAbstract::VERSION => $this->version,
            StrategyAbstract::DATA => $this->data->toArray(),
        ];
    }
}
