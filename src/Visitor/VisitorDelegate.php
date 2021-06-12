<?php

namespace Flagship\Visitor;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipContext;
use Flagship\Hit\HitAbstract;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\ContainerInterface;

class VisitorDelegate extends VisitorAbstract
{
    /**
     * Create a new VisitorDelegate.
     *
     * @param ContainerInterface $dependencyIContainer
     * @param ConfigManager $configManager
     * @param string $visitorId : visitor unique identifier.
     * @param array $context : visitor context. e.g: ["age"=>42, "isVip"=>true, "country"=>"UK"]
     */
    public function __construct(
        ContainerInterface $dependencyIContainer,
        ConfigManager $configManager,
        $visitorId,
        array $context = []
    ) {
        $this->setDependencyIContainer($dependencyIContainer);
        $this->setConfig($configManager->getConfig());
        $this->setVisitorId($visitorId);
        $this->setContext($context);
        $this->setConfigManager($configManager);
        $this->loadPredefinedContext();
    }

    private function getRealVisitorIp()
    {
        $realIp = getenv('HTTP_X_REAL_IP');
        $clientIp = getenv('HTTP_CLIENT_IP');
        $forwardedIp = getenv('HTTP_X_FORWARDED_FOR');

        switch (true) {
            case (!empty($realIp)):
                $ip = $realIp;
                break;
            case (!empty($clientIp)):
                $ip =  $clientIp;
                break;
            case (!empty($forwardedIp)):
                $ip =  $forwardedIp;
                break;
            default:
                $ip = getenv('REMOTE_ADDR');
                break;
        }
        return $ip;
    }

    private function loadPredefinedContext()
    {
        $defaultContext = [
            FlagshipContext::OS_NAME => PHP_OS,
            FlagshipContext::DEVICE_TYPE => "server"
        ];

        $ip = $this->getRealVisitorIp();
        if ($ip) {
            $defaultContext [FlagshipContext::IP] = $ip;
        }

        $this->updateContextCollection($defaultContext);

        $this->context[FlagshipConstant::FS_CLIENT] = FlagshipConstant::SDK_LANGUAGE;
        $this->context[FlagshipConstant::FS_VERSION] = FlagshipConstant::SDK_VERSION;
        $this->context[FlagshipConstant::FS_USERS] = $this->getVisitorId();
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
        $this->loadPredefinedContext();
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
