<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;

class DecisionApiConfig extends FlagshipConfig
{
    /**
     * @param string $envId
     * @param string $apiKey
     */
    public function __construct($envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::DECISION_API);
    }
}
