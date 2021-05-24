<?php

namespace Flagship\Visitor;

use Flagship\Hit\HitAbstract;
use Flagship\Utils\ConfigManager;

class VisitorDelegate extends VisitorAbstract
{
    /**
     * Create a new VisitorDelegate.
     *
     * @param ConfigManager $configManager
     * @param string $visitorId : visitor unique identifier.
     * @param array $context : visitor context. e.g: ["age"=>42, "isVip"=>true, "country"=>"UK"]
     */
    public function __construct(ConfigManager $configManager, $visitorId, array $context = [])
    {
        $this->setConfig($configManager->getConfig());
        $this->setVisitorId($visitorId);
        $this->updateContextCollection($context);
        $this->setConfigManager($configManager);
    }
    /**
     * @inheritDoc
     */
    public function updateContext($key, $value)
    {
        $this->getStrategy()->updateContext($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context)
    {
        $this->getStrategy()->updateContextCollection($context);
    }

    /**
     * @inheritDoc
     */
    public function clearContext()
    {
        $this->getStrategy()->clearContext();
    }

    /**
     * @inheritDoc
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        return $this->getStrategy()->getModification($key, $defaultValue, $activate);
    }

    /**
     * @inheritDoc
     */
    public function getModificationInfo($key)
    {
        return $this->getStrategy()->getModificationInfo($key);
    }

    /**
     * @inheritDoc
     */
    public function synchronizedModifications()
    {
        $this->getStrategy()->synchronizedModifications();
    }


    /**
     * @inheritDoc
     */
    public function activateModification($key)
    {
        $this->getStrategy()->activateModification($key);
    }

    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        $this->getStrategy()->sendHit($hit);
    }
}
