<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;

class BucketingConfig extends FlagshipConfig
{
    /**
     * @var int
     */
    private $pollingInterval = FlagshipConstant::REQUEST_TIME_OUT;

    /**
     * @var string
     */
    private $baseBucketingDirectory;

    /**
     * @var string
     */
    private $bucketingDirectory;

    public function __construct($envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::BUCKETING);
        $this->setBucketingDirectory("");
    }

    /**
     * @return int
     */
    public function getPollingInterval()
    {
        return $this->pollingInterval * 1000;
    }

    /**
     * Specify delay between two bucketing polling.
     *     Note: If 0 is given then it should poll only once at start time.
     * @param int $pollingInterval : time delay in second. Default is 2000ms.
     * @return BucketingConfig
     */
    public function setPollingInterval($pollingInterval)
    {
        if (!$this->isNumeric($pollingInterval, "pollingInterval", $this)) {
            return $this;
        }
        $this->pollingInterval = $pollingInterval / 1000;
        return $this;
    }

    /**
     * @return string
     */
    public function getBucketingDirectory()
    {
        return $this->bucketingDirectory;
    }

    /**
     * @param string $bucketingDirectory
     * @return BucketingConfig
     */
    public function setBucketingDirectory($bucketingDirectory)
    {
        $this->baseBucketingDirectory = $bucketingDirectory;
        if (empty($bucketingDirectory)) {
            $this->baseBucketingDirectory = FlagshipConstant::BUCKETING_DIRECTORY;
            $bucketingDirectory = __DIR__ . '/../../' . FlagshipConstant::BUCKETING_DIRECTORY;
        } else {
            $bucketingDirectory = __DIR__ . '/../../../../../' . $bucketingDirectory;
        }
        $this->bucketingDirectory = $bucketingDirectory;
        return $this;
    }

    public function jsonSerialize()
    {
        $parent = parent::jsonSerialize();
        $parent[FlagshipField::FIELD_POLLING_INTERVAL] = $this->getPollingInterval();
        $parent[FlagshipField::FIELD_BUCKETING_DIRECTORY] = $this->baseBucketingDirectory;
        return $parent;
    }
}
