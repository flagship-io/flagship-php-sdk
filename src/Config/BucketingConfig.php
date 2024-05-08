<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;

class BucketingConfig extends FlagshipConfig
{
    /**
     * @var string
     */
    private $syncAgentUrl;

    /**
     * @var bool
     */
    protected $fetchThirdPartyData;

    /**
     * @param string $syncAgentUrl
     * @param string $envId
     * @param string $apiKey
     */
    public function __construct($syncAgentUrl, $envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::BUCKETING);
        $this->setSyncAgentUrl($syncAgentUrl);
    }

    /**
     * @return string
     */
    public function getSyncAgentUrl()
    {
        return $this->syncAgentUrl;
    }

    /**
     * Define the [flagship-sync-agent](https://github.com/flagship-io/flagship-sync-agent) endpoint URL where the SDK will fetch the bucketing file from polling process
     * @param string $syncAgentUrl
     * @return BucketingConfig
     */
    public function setSyncAgentUrl($syncAgentUrl)
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
    public function getFetchThirdPartyData()
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
    public function setFetchThirdPartyData($fetchThirdPartyData)
    {
        $this->fetchThirdPartyData = $fetchThirdPartyData;
        return $this;
    }

    /**
     * @inheritDoc
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $parent = parent::jsonSerialize();
        $parent[FlagshipField::FIELD_BUCKETING_URL] = $this->syncAgentUrl;
        return $parent;
    }
}
