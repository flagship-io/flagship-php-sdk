<?php

namespace Flagship\Visitor;

use Flagship\Api\TrackingManagerAbstract;
use Flagship\Config\FlagshipConfig;
use Flagship\Decision\DecisionManagerAbstract;
use Flagship\Enum\FlagshipConstant;
use Flagship\Traits\HasSameTypeTrait;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\ConfigManager;

abstract class VisitorStrategyAbstract implements VisitorCoreInterface, VisitorFlagInterface
{
    use ValidatorTrait;
    use HasSameTypeTrait;

    /**
     * @var VisitorAbstract
     */
    protected $visitor;

    public function __construct(VisitorAbstract $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @return VisitorAbstract
     */
    protected function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->getVisitor()->getConfigManager();
    }

    /**
     * @return FlagshipConfig
     */
    protected function getConfig()
    {
        return $this->getVisitor()->getConfig();
    }

    /**
     * @param string $process
     * @return TrackingManagerAbstract|null
     */
    protected function getTrackingManager($process = null)
    {
        $trackingManager = $this->getConfigManager()->getTrackingManager();

        if (!$trackingManager) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::TRACKER_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
        }
        return $trackingManager;
    }

    /**
     * @param string $process
     * @return DecisionManagerAbstract|null
     */
    protected function getDecisionManager($process = null)
    {
        $decisionManager = $this->getConfigManager()->getDecisionManager();
        if (!$decisionManager) {
            $this->logError(
                $this->getVisitor()->getConfig(),
                FlagshipConstant::DECISION_MANAGER_MISSING_ERROR,
                [FlagshipConstant::TAG => $process]
            );
        }
        return $decisionManager;
    }
}
