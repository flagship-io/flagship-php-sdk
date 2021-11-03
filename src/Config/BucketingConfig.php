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
    private $pollingInterval = FlagshipConstant::DEFAULT_POLLING_INTERVAL;

    /**
     * @var string
     */
    private $baseBucketingDirectory;

    /**
     * @var string
     */
    private $bucketingDirectoryPath;

    public function __construct($envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::BUCKETING);
        $this->setBucketingDirectoryPath("");
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
    public function getBucketingDirectoryPath()
    {
        return $this->bucketingDirectoryPath;
    }

    /**
     * Define the directory path  where the SDK will find the bucketing file from polling process
     *     Note: Default path is `/webroot/vendor/flagship-io/flagship`
     * @param string $bucketingDirectoryPath : directory path
     * @return BucketingConfig
     */
    public function setBucketingDirectoryPath($bucketingDirectoryPath)
    {
        $this->baseBucketingDirectory = $bucketingDirectoryPath;
        if (empty($bucketingDirectoryPath)) {
            $this->baseBucketingDirectory = FlagshipConstant::BUCKETING_DIRECTORY;
            $bucketingDirectoryPath = __DIR__ . '/../../' . FlagshipConstant::BUCKETING_DIRECTORY;
        } else {
            $bucketingDirectoryPath = __DIR__ . '/../../../../../' . $bucketingDirectoryPath;
        }
        $this->bucketingDirectoryPath = $bucketingDirectoryPath;
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
