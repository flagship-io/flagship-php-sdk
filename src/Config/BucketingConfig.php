<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;

class BucketingConfig extends FlagshipConfig
{
    private $bucketingUrl;


    public function __construct($bucketingUrl, $envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::BUCKETING);
        $this->setBucketingUrl($bucketingUrl);
    }

    /**
     * @return mixed
     */
    public function getBucketingUrl()
    {
        return $this->bucketingUrl;
    }

    /**
     * @param mixed $bucketingUrl
     * @return BucketingConfig
     */
    public function setBucketingUrl($bucketingUrl)
    {
        $this->bucketingUrl = $bucketingUrl;
        return $this;
    }

    public function jsonSerialize()
    {
        $parent = parent::jsonSerialize();
        $parent[FlagshipField::FIELD_BUCKETING_URL] = $this->bucketingUrl;
        return $parent;
    }
}
