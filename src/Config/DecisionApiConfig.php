<?php

namespace Flagship\Config;

use Flagship\Enum\DecisionMode;

class DecisionApiConfig extends FlagshipConfig
{
    /**
     * @param string|null $envId
     * @param string|null $apiKey
     */
    public function __construct(?string $envId = null, ?string $apiKey = null)
    {
        parent::__construct($envId, $apiKey);
        $this->setDecisionMode(DecisionMode::DECISION_API);
    }
}
