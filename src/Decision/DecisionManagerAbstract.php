<?php

namespace Flagship\Decision;

use Flagship\Api\TrackingManagerInterface;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FlagshipStatus;
use Flagship\Model\FlagDTO;
use Flagship\Model\TroubleshootingData;
use Flagship\Traits\BuildApiTrait;
use Flagship\Traits\Helper;
use Flagship\Traits\ValidatorTrait;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor\VisitorAbstract;

abstract class DecisionManagerAbstract implements DecisionManagerInterface
{
    use ValidatorTrait;
    use BuildApiTrait;
    use Helper;

    /**
     * @var bool
     */
    protected $isPanicMode = false;
    /**
     * @var HttpClientInterface
     */
    protected $httpClient;
    /**
     * @var callable
     */
    private $statusChangedCallback;

    /**
     * @var FlagshipConfig
     */
    protected $config;

    /**
     * @var TroubleshootingData
     */
    protected $troubleshootingData;

    /**
     * @var TrackingManagerInterface
     */
    protected $trackingManager;
    /**
     * @var string
     */
    protected $flagshipInstanceId;

    /**
     * ApiManager constructor.
     *
     * @param HttpClientInterface $httpClient
     * @param FlagshipConfig $config
     */
    public function __construct(HttpClientInterface $httpClient, FlagshipConfig $config)
    {
        $this->httpClient = $httpClient;
        $this->setConfig($config);
    }

    /**
     * @return TrackingManagerInterface
     */
    public function getTrackingManager()
    {
        return $this->trackingManager;
    }

    /**
     * @param TrackingManagerInterface $trackingManager
     * @return DecisionManagerAbstract
     */
    public function setTrackingManager($trackingManager)
    {
        $this->trackingManager = $trackingManager;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagshipInstanceId()
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param string $flagshipInstanceId
     * @return DecisionManagerAbstract
     */
    public function setFlagshipInstanceId($flagshipInstanceId)
    {
        $this->flagshipInstanceId = $flagshipInstanceId;
        return $this;
    }
    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return bool
     */
    public function getIsPanicMode()
    {
        return $this->isPanicMode;
    }

    /**
     * @param bool $isPanicMode
     * @return DecisionManagerAbstract
     */
    public function setIsPanicMode($isPanicMode)
    {
        $status = $isPanicMode ? FlagshipStatus::READY_PANIC_ON : FlagshipStatus::READY;
        $this->updateFlagshipStatus($status);

        $this->isPanicMode = $isPanicMode;
        return $this;
    }

    /**
     * Define a callable in order to get callback when the SDK status has changed.
     * @param callable $statusChangedCallback callback
     * @return DecisionManagerAbstract
     */
    public function setStatusChangedCallback($statusChangedCallback)
    {
        if (is_callable($statusChangedCallback)) {
            $this->statusChangedCallback = $statusChangedCallback;
        }
        return $this;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return DecisionManagerAbstract
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param $newStatus
     * @return void
     */
    protected function updateFlagshipStatus($newStatus)
    {
        $callable = $this->statusChangedCallback;
        if ($callable) {
            call_user_func($callable, $newStatus);
        }
    }

    /**
     * @param  FlagDTO[] $modifications
     * @param  $key
     * @return FlagDTO|null
     */
    protected function checkModificationKeyExist(array $modifications, $key)
    {
        foreach ($modifications as $modification) {
            if ($modification->getKey() === $key) {
                return $modification;
            }
        }
        return null;
    }

    /**
     * Return an array of Modification from all campaigns
     *
     * @param  $campaigns
     * @return FlagDTO[] Return an array of Modification
     */
    public function getModifications($campaigns)
    {

        $modifications = [];
        foreach ($campaigns as $campaign) {
            if (
                !isset($campaign[FlagshipField::FIELD_VARIATION])
                || !isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS])
                || !isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS]
                    [FlagshipField::FIELD_VALUE])
            ) {
                continue;
            }

            $modificationValues = $campaign[FlagshipField::FIELD_VARIATION]
            [FlagshipField::FIELD_MODIFICATIONS][FlagshipField::FIELD_VALUE];

            $modifications = $this->getModificationValues($modificationValues, $campaign, $modifications);
        }
        return $modifications;
    }

    /**
     * Return modification of a campaign
     *
     * @param  array $modificationValues
     * @param  $campaign
     * @param  array $modifications
     * @return array
     */
    protected function getModificationValues(array $modificationValues, $campaign, $modifications)
    {
        $localModifications = [];
        foreach ($modificationValues as $key => $modificationValue) {
            if (!$this->isKeyValid($key)) {
                continue;
            }

            //check if the key is already used
            $modification = $this->checkModificationKeyExist($modifications, $key);
            $isKeyUsed = true;

            if (is_null($modification)) {
                $modification = new FlagDTO();
                $isKeyUsed = false;
            }

            $modification->setKey($key);
            $modification->setValue($modificationValue);

            if (isset($campaign[FlagshipField::FIELD_ID])) {
                $modification->setCampaignId($campaign[FlagshipField::FIELD_ID]);
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION_GROUP_ID])) {
                $modification->setVariationGroupId($campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]);
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_ID])) {
                $modification->setVariationId(
                    $campaign[FlagshipField::FIELD_VARIATION]
                    [FlagshipField::FIELD_ID]
                );
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_REFERENCE])) {
                $modification->setIsReference(
                    $campaign[FlagshipField::FIELD_VARIATION]
                    [FlagshipField::FIELD_REFERENCE]
                );
            }

            if (isset($campaign[FlagshipField::FIELD_CAMPAIGN_TYPE])) {
                $modification->setCampaignType($campaign[FlagshipField::FIELD_CAMPAIGN_TYPE]);
            }

            if (isset($campaign[FlagshipField::FIELD_SLUG])) {
                $modification->setSlug($campaign[FlagshipField::FIELD_SLUG]);
            }

            if (!$isKeyUsed) {
                $localModifications[] = $modification;
            }
        }
        return array_merge($modifications, $localModifications);
    }

    /**
     * @param VisitorAbstract $visitor
     * @return array
     */
    abstract public function getCampaigns(VisitorAbstract $visitor);

    /**
     * @inheritDoc
     */
    public function getCampaignModifications(VisitorAbstract $visitor)
    {
        $campaigns = $this->getCampaigns($visitor);
        return $this->getModifications($campaigns);
    }

    public function getTroubleshootingData()
    {
        return $this->troubleshootingData;
    }
}
