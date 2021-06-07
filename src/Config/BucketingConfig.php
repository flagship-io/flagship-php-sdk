<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\FlagshipConfig;

class BucketingConfig extends FlagshipConfig
{

    public function __construct($envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::BUCKETING);
    }

    /**
     * @var int
     */
    private $pollingInterval = FlagshipConstant::REQUEST_TIME_OUT;

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
    public function jsonSerialize()
    {
        $parent = parent::jsonSerialize();
        $parent[FlagshipField::FIELD_POLLING_INTERVAL] = $this->getPollingInterval();
        return $parent;
    }
}
