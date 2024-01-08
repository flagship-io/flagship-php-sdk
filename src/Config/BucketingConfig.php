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
    private $bucketingUrl;

    /**
     * @var bool
     */
    protected $fetchThirdPartyData;

    /**
     * @param string $bucketingUrl
     * @param string $envId
     * @param string $apiKey
     */
    public function __construct($bucketingUrl, $envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::BUCKETING);
        $this->setBucketingUrl($bucketingUrl);
    }

    /**
     * @return string
     */
    public function getBucketingUrl()
    {
        return $this->bucketingUrl;
    }

    /**
     * @param string $bucketingUrl
     * @return BucketingConfig
     */
    public function setBucketingUrl($bucketingUrl)
    {
        $this->bucketingUrl = $bucketingUrl;
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
        $parent[FlagshipField::FIELD_BUCKETING_URL] = $this->bucketingUrl;
        return $parent;
    }
}
