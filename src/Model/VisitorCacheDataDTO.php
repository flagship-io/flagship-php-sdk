<?php

namespace Flagship\Model;

use Flagship\Visitor\StrategyAbstract;

/**
 * @phpstan-import-type VisitorCacheDataArray from Types
 */
class VisitorCacheDataDTO
{
    private string $visitorId;

    private ?string $anonymousId;

    private ?bool $consent = null;

    /** @var array<string, scalar>|null */
    private ?array $context = null;

    /** @var array<string, string>|null */
    private ?array $assignmentsHistory = null;

    /** @var array<CampaignCacheDTO>|null */
    private ?array $campaigns = null;

    public function __construct(string $visitorId, ?string $anonymousId = null)
    {
        $this->visitorId = $visitorId;
        $this->anonymousId = $anonymousId;
    }

    public function getVisitorId(): string
    {
        return $this->visitorId;
    }

    public function setVisitorId(string $visitorId): self
    {
        $this->visitorId = $visitorId;
        return $this;
    }

    public function getAnonymousId(): ?string
    {
        return $this->anonymousId;
    }

    public function setAnonymousId(?string $anonymousId): self
    {
        $this->anonymousId = $anonymousId;
        return $this;
    }

    public function getConsent(): ?bool
    {
        return $this->consent;
    }

    public function setConsent(?bool $consent): self
    {
        $this->consent = $consent;
        return $this;
    }

    /**
     * @return array<string, scalar>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param array<string, scalar>|null $context
     */
    public function setContext(?array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getAssignmentsHistory(): ?array
    {
        return $this->assignmentsHistory;
    }

    /**
     * @param array<string, string>|null $assignmentsHistory
     */
    public function setAssignmentsHistory(?array $assignmentsHistory): self
    {
        $this->assignmentsHistory = $assignmentsHistory;
        return $this;
    }

    /**
     * @return array<CampaignCacheDTO>|null
     */
    public function getCampaigns(): ?array
    {
        return $this->campaigns;
    }

    /**
     * @param array<CampaignCacheDTO>|null $campaigns
     */
    public function setCampaigns(?array $campaigns): self
    {
        $this->campaigns = $campaigns;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $visitorId = $data[StrategyAbstract::VISITOR_ID] ?? '';
        $anonymousId = $data[StrategyAbstract::ANONYMOUS_ID] ?? null;

        $instance = new self(
            is_string($visitorId) ? $visitorId : '',
            is_string($anonymousId) ? $anonymousId : null
        );

        if (isset($data[StrategyAbstract::CONSENT]) && is_bool($data[StrategyAbstract::CONSENT])) {
            $instance->setConsent($data[StrategyAbstract::CONSENT]);
        }

        if (isset($data[StrategyAbstract::CONTEXT]) && is_array($data[StrategyAbstract::CONTEXT])) {
            $context = array_filter(
                $data[StrategyAbstract::CONTEXT],
                fn($value, $key) => is_string($key) && is_scalar($value),
                ARRAY_FILTER_USE_BOTH
            );
            $instance->setContext($context ?: null);
        }

        $assignmentsHistory = [];

        if (isset($data[StrategyAbstract::ASSIGNMENTS_HISTORY]) && is_array($data[StrategyAbstract::ASSIGNMENTS_HISTORY])) {
            $assignmentsHistory = array_filter(
                $data[StrategyAbstract::ASSIGNMENTS_HISTORY],
                fn($value, $key) => is_string($key) && is_string($value),
                ARRAY_FILTER_USE_BOTH
            );
        }
        $instance->setAssignmentsHistory($assignmentsHistory);

        $campaigns = [];
        if (isset($data[StrategyAbstract::CAMPAIGNS]) && is_array($data[StrategyAbstract::CAMPAIGNS])) {
            $campaigns = array_map(
                fn($campaign) => is_array($campaign) ? CampaignCacheDTO::fromArray($campaign) : null,
                $data[StrategyAbstract::CAMPAIGNS]
            );
            $campaigns = array_filter($campaigns);
        }
        $instance->setCampaigns($campaigns );

        return $instance;
    }

    /**
     * @return VisitorCacheDataArray
     */
    public function toArray(): array
    {
        $result = [
            StrategyAbstract::VISITOR_ID => $this->visitorId,
            StrategyAbstract::ANONYMOUS_ID => $this->anonymousId,
        ];

        if ($this->consent !== null) {
            $result[StrategyAbstract::CONSENT] = $this->consent;
        }

        if ($this->context !== null) {
            $result[StrategyAbstract::CONTEXT] = $this->context;
        }

        if ($this->assignmentsHistory !== null) {
            $result[StrategyAbstract::ASSIGNMENTS_HISTORY] = $this->assignmentsHistory;
        }

        if ($this->campaigns !== null) {
            $result[StrategyAbstract::CAMPAIGNS] = array_map(
                fn(CampaignCacheDTO $campaign) => $campaign->toArray(),
                $this->campaigns
            );
        }

        return $result;
    }
}
