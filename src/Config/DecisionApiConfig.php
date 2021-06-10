<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;

class DecisionApiConfig extends FlagshipConfig
{
    public function __construct($envId = null, $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::DECISION_API);
    }
}
