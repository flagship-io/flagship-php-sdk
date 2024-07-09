<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipField;

class BucketingConfig extends FlagshipConfig
{
    /**
     * @var string
     */
    private string $syncAgentUrl;

    /**
     * @var bool
     */
    protected bool $fetchThirdPartyData;

    /**
     * @param string $syncAgentUrl
     * @param string|null $envId
     * @param string|null $apiKey
     */
    public function __construct(string $syncAgentUrl, ?string $envId = null, ?string $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::BUCKETING);
        $this->setSyncAgentUrl($syncAgentUrl);
        $this->setFetchThirdPartyData(false);
    }

    /**
     * @return string
     */
    public function getSyncAgentUrl(): string
    {
        return $this->syncAgentUrl;
    }

    /**
     * Define the [flagship-sync-agent](https://github.com/flagship-io/flagship-sync-agent)
     * endpoint URL where the SDK will fetch the bucketing file from polling process
     * @param string $syncAgentUrl
     * @return BucketingConfig
     */
    public function setSyncAgentUrl(string $syncAgentUrl): static
    {
        $this->syncAgentUrl = $syncAgentUrl;
        return $this;
    }

    /**
     * If true is set, the visitor's segment will be fetched from
     * [universal data connector](https://developers.abtasty.com/docs/data/universal-data-connector)
     * each time fetchFlags is called and append those segments in the visitor context
     * @return bool
     */
    public function getFetchThirdPartyData(): bool
    {
        return $this->fetchThirdPartyData;
    }

    /**
     * If you set true, it will fetch the visitor's segment from
     * [universal data connector](https://developers.abtasty.com/docs/data/universal-data-connector)
     * each time fetchFlags is called and append those segments in the visitor context
     * @param bool $fetchThirdPartyData
     * @return BucketingConfig
     */
    public function setFetchThirdPartyData(bool $fetchThirdPartyData): static
    {
        $this->fetchThirdPartyData = $fetchThirdPartyData;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $parent = parent::jsonSerialize();
        $parent[FlagshipField::FIELD_BUCKETING_URL] = $this->syncAgentUrl;
        return $parent;
    }
}
