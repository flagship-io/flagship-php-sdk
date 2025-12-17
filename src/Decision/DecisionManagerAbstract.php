<?php

namespace Flagship\Decision;

use Flagship\Api\TrackingManagerInterface;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSSdkStatus;
use Flagship\Model\CampaignDTO;
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
     * @var ?callable
     */
    private $statusChangedCallback = null;

    /**
     * @var FlagshipConfig
     */
    protected FlagshipConfig $config;

    /**
     * @var ?TroubleshootingData
     */
    protected ?TroubleshootingData $troubleshootingData = null;

    /**
     * @var TrackingManagerInterface
     */
    protected TrackingManagerInterface $trackingManager;
    /**
     * @var ?string
     */
    protected ?string $flagshipInstanceId = null;

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
    public function setTrackingManager(TrackingManagerInterface $trackingManager): self
    {
        $this->trackingManager = $trackingManager;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getFlagshipInstanceId(): ?string
    {
        return $this->flagshipInstanceId;
    }

    /**
     * @param ?string $flagshipInstanceId
     * @return DecisionManagerAbstract
     */
    public function setFlagshipInstanceId(?string $flagshipInstanceId): self
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
    public function setIsPanicMode(bool $isPanicMode): self
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
    public function setStatusChangedCallback(callable $statusChangedCallback): self
    {
        $this->statusChangedCallback = $statusChangedCallback;
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
    public function setConfig(FlagshipConfig $config): self
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
     * Return an array of flags from all campaigns
     *
     * @param  CampaignDTO[] $campaigns
     * @return FlagDTO[] Return an array of flags
     */
    public function getFlagsData(array $campaigns): array
    {
        /** @var array<string, FlagDTO> $existingFlags*/
        $existingFlags = [];

        foreach ($campaigns as $campaign) {

            if (empty($campaign->getVariationGroupId())) {
                continue;
            }

            $existingFlags = $this->extractFlagsFromCampaign($campaign, $existingFlags);
        }
        return array_values($existingFlags);
    }

    /**
     * Return modification of a campaign
     *
     * @param CampaignDTO $campaign
     * @param array<string, FlagDTO> $existingFlags
     * @return array<string, FlagDTO>
     */
    protected function extractFlagsFromCampaign(CampaignDTO $campaign, array $existingFlags): array
    {
        $flagsValue = $campaign->getVariation()->getModifications()->getValue();

        foreach ($flagsValue as $key => $flagValue) {
            if (!is_string($key) || empty($key)) {
                continue;
            }

            $flagDTO = $existingFlags[$key] ?? new FlagDTO();

            $flagDTO->setKey($key);
            $flagDTO->setValue($flagValue);

            $flagDTO->setCampaignId($campaign->getId());

            if (!empty($campaign->getName())) {
                $flagDTO->setCampaignName($campaign->getName());
            }

            if (!empty($campaign->getType())) {
                $flagDTO->setCampaignType($campaign->getType());
            }

            if (!empty($campaign->getVariationGroupId())) {
                $flagDTO->setVariationGroupId($campaign->getVariationGroupId());
            }


            if (!empty($campaign->getVariationGroupName())) {
                $flagDTO->setVariationGroupName($campaign->getVariationGroupName());
            }

            if (!empty($campaign->getVariation()->getId())) {
                $flagDTO->setVariationId(
                    $campaign->getVariation()->getId()
                );
            }

            if (!empty($campaign->getVariation()->getName())) {
                $flagDTO->setVariationName($campaign->getVariation()->getName());
            }

            $flagDTO->setIsReference(
                $campaign->getVariation()->getReference() ?? false
            );

            if (!empty($campaign->getSlug())) {
                $flagDTO->setSlug($campaign->getSlug());
            }
            $existingFlags[$key] = $flagDTO;
        }
        return $existingFlags;
    }

    /**
     * @inheritDoc
     */
    abstract public function getCampaigns(VisitorAbstract $visitor): array|null;

    /**
     * @inheritDoc
     */
    public function getCampaignFlags(VisitorAbstract $visitor): array
    {
        $campaigns = $this->getCampaigns($visitor);
        if (is_null($campaigns) || empty($campaigns)) {
            return [];
        }
        return $this->getFlagsData($campaigns);
    }

    public function getTroubleshootingData(): ?TroubleshootingData
    {
        return $this->troubleshootingData;
    }
}
