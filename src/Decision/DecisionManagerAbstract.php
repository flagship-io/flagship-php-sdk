<?php

namespace Flagship\Decision;

use Flagship\Api\TrackingManagerInterface;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSSdkStatus;
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
    protected bool $isPanicMode = false;
    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;
    /**
     * @var callable
     */
    private $statusChangedCallback;

    /**
     * @var FlagshipConfig
     */
    protected FlagshipConfig $config;

    /**
     * @var ?TroubleshootingData
     */
    protected ?TroubleshootingData $troubleshootingData;

    /**
     * @var TrackingManagerInterface
     */
    protected TrackingManagerInterface $trackingManager;
    /**
     * @var string
     */
    protected string $flagshipInstanceId;

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
    public function getTrackingManager(): TrackingManagerInterface
    {
        return $this->trackingManager;
    }

    /**
     * @param TrackingManagerInterface $trackingManager
     * @return DecisionManagerAbstract
     */
    public function setTrackingManager(TrackingManagerInterface $trackingManager): static
    {
        $this->trackingManager = $trackingManager;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagshipInstanceId(): string
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param string $flagshipInstanceId
     * @return DecisionManagerAbstract
     */
    public function setFlagshipInstanceId(string $flagshipInstanceId): static
    {
        $this->flagshipInstanceId = $flagshipInstanceId;
        return $this;
    }
    /**
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @return bool
     */
    public function getIsPanicMode(): bool
    {
        return $this->isPanicMode;
    }

    /**
     * @param bool $isPanicMode
     * @return DecisionManagerAbstract
     */
    public function setIsPanicMode(bool $isPanicMode): static
    {
        $status = $isPanicMode ? FSSdkStatus::SDK_PANIC : FSSdkStatus::SDK_INITIALIZED;
        $this->updateFlagshipStatus($status);

        $this->isPanicMode = $isPanicMode;
        return $this;
    }

    /**
     * Define a callable in order to get callback when the SDK status has changed.
     * @param callable $statusChangedCallback callback
     * @return DecisionManagerAbstract
     */
    public function setStatusChangedCallback(callable $statusChangedCallback): static
    {
        if (is_callable($statusChangedCallback)) {
            $this->statusChangedCallback = $statusChangedCallback;
        }
        return $this;
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig(): FlagshipConfig
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return DecisionManagerAbstract
     */
    public function setConfig(FlagshipConfig $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param FSSdkStatus $newStatus
     * @return void
     */
    protected function updateFlagshipStatus(FSSdkStatus $newStatus): void
    {
        $callable = $this->statusChangedCallback;
        if ($callable) {
            call_user_func($callable, $newStatus);
        }
    }

    /**
     * @param FlagDTO[] $flags
     * @param string $key
     * @return FlagDTO|null
     */
    protected function checkFlagKeyExist(array $flags, string $key): ?FlagDTO
    {
        foreach ($flags as $flag) {
            if ($flag->getKey() === $key) {
                return $flag;
            }
        }
        return null;
    }

    /**
     * Return an array of flags from all campaigns
     *
     * @param  $campaigns
     * @return FlagDTO[] Return an array of flags
     */
    public function getFlagsData($campaigns): array
    {

        $flags = [];
        foreach ($campaigns as $campaign) {
            if (
                !isset($campaign[FlagshipField::FIELD_VARIATION])
                || !isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS])
                || !isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS]
                    [FlagshipField::FIELD_VALUE])
            ) {
                continue;
            }

            $flagsValue = $campaign[FlagshipField::FIELD_VARIATION]
            [FlagshipField::FIELD_MODIFICATIONS][FlagshipField::FIELD_VALUE];

            $flags = $this->getFlagsValue($flagsValue, $campaign, $flags);
        }
        return $flags;
    }

    /**
     * Return modification of a campaign
     *
     * @param array $flagsValue
     * @param array $campaign
     * @param array $flagsDTO
     * @return array
     */
    protected function getFlagsValue(array $flagsValue, array $campaign, array $flagsDTO): array
    {
        $localFlags = [];
        foreach ($flagsValue as $key => $flagValue) {
            if (!$this->isKeyValid($key)) {
                continue;
            }

            //check if the key is already used
            $flagDTO = $this->checkFlagKeyExist($flagsDTO, $key);
            $isKeyUsed = true;

            if (is_null($flagDTO)) {
                $flagDTO = new FlagDTO();
                $isKeyUsed = false;
            }

            $flagDTO->setKey($key);
            $flagDTO->setValue($flagValue);

            if (isset($campaign[FlagshipField::FIELD_ID])) {
                $flagDTO->setCampaignId($campaign[FlagshipField::FIELD_ID]);
            }

            if (isset($campaign[FlagshipField::FIELD_CAMPAIGNS_NAME])) {
                $flagDTO->setCampaignName($campaign[FlagshipField::FIELD_CAMPAIGNS_NAME]);
            }

            if (isset($campaign[FlagshipField::FIELD_CAMPAIGN_TYPE])) {
                $flagDTO->setCampaignType($campaign[FlagshipField::FIELD_CAMPAIGN_TYPE]);
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION_GROUP_ID])) {
                $flagDTO->setVariationGroupId($campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]);
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION_GROUP_NAME])) {
                $flagDTO->setVariationGroupName($campaign[FlagshipField::FIELD_VARIATION_GROUP_NAME]);
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_ID])) {
                $flagDTO->setVariationId(
                    $campaign[FlagshipField::FIELD_VARIATION]
                    [FlagshipField::FIELD_ID]
                );
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_NANE])) {
                $flagDTO->setVariationName($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_NANE]);
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_REFERENCE])) {
                $flagDTO->setIsReference(
                    $campaign[FlagshipField::FIELD_VARIATION]
                    [FlagshipField::FIELD_REFERENCE]
                );
            }

            if (isset($campaign[FlagshipField::FIELD_SLUG])) {
                $flagDTO->setSlug($campaign[FlagshipField::FIELD_SLUG]);
            }

            if (!$isKeyUsed) {
                $localFlags[] = $flagDTO;
            }
        }
        return array_merge($flagsDTO, $localFlags);
    }

    /**
     * @param VisitorAbstract $visitor
     * @return array
     */
    abstract public function getCampaigns(VisitorAbstract $visitor): array;

    /**
     * @inheritDoc
     */
    public function getCampaignFlags(VisitorAbstract $visitor): array
    {
        $campaigns = $this->getCampaigns($visitor);
        return $this->getFlagsData($campaigns);
    }

    public function getTroubleshootingData(): ?TroubleshootingData
    {
        return $this->troubleshootingData;
    }
}
