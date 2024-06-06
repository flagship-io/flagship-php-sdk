<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use Flagship\Flag\FSFlagMetadataInterface;

class Activate extends HitAbstract
{
    public const ERROR_MESSAGE  = 'variationId and variationGroupId are required';

    /**
     * @var string
     */
    private string $variationGroupId;

    /**
     * @var string
     */
    private string $variationId;

    /**
     * @var string
     */
    private string $flagKey;

    /**
     * @var  bool|numeric|string|array|null
     */
    private string|array|bool|int|float|null $flagValue;

    /**
     * @var array
     */
    private array $visitorContext;

    /**
     * @var FSFlagMetadataInterface
     */
    private FSFlagMetadataInterface $flagMetadata;

    /**
     * @var  bool|numeric|string|array|null
     */
    private string|array|bool|int|float|null $flagDefaultValue;

    public static function getClassName(): string
    {
        return __CLASS__;
    }



    /**
     * @param string $variationGroupId
     * @param string $variationId
     */
    public function __construct(string $variationGroupId, string $variationId)
    {
        parent::__construct(HitType::ACTIVATE);
        $this->variationGroupId = $variationGroupId;
        $this->variationId = $variationId;
    }

    /**
     * @return string
     */
    public function getVariationGroupId(): string
    {
        return $this->variationGroupId;
    }

    /**
     * @param string $variationGroupId
     * @return Activate
     */
    public function setVariationGroupId(string $variationGroupId): static
    {
        $this->variationGroupId = $variationGroupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariationId(): string
    {
        return $this->variationId;
    }

    /**
     * @param string $variationId
     * @return Activate
     */
    public function setVariationId(string $variationId): static
    {
        $this->variationId = $variationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlagKey(): string
    {
        return $this->flagKey;
    }

    /**
     * @param string $flagKey
     * @return Activate
     */
    public function setFlagKey(string $flagKey): static
    {
        $this->flagKey = $flagKey;
        return $this;
    }

    /**
     * @return  bool|numeric|string|array|null
     */
    public function getFlagValue(): float|array|bool|int|string|null
    {
        return $this->flagValue;
    }

    /**
     * @param array|bool|string|numeric|null $flagValue
     * @return Activate
     */
    public function setFlagValue(float|array|bool|int|string|null $flagValue): static
    {
        $this->flagValue = $flagValue;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getVisitorContext(): array
    {
        return $this->visitorContext;
    }

    /**
     * @param array $visitorContext
     * @return Activate
     */
    public function setVisitorContext(array $visitorContext): static
    {
        $this->visitorContext = $visitorContext;
        return $this;
    }

    /**
     * @return FSFlagMetadataInterface
     */
    public function getFlagMetadata(): FSFlagMetadataInterface
    {
        return $this->flagMetadata;
    }

    /**
     * @param FSFlagMetadataInterface $flagMetadata
     * @return Activate
     */
    public function setFlagMetadata(FSFlagMetadataInterface $flagMetadata): static
    {
        $this->flagMetadata = $flagMetadata;
        return $this;
    }

    /**
     * @return  bool|numeric|string|array|null
     */
    public function getFlagDefaultValue(): float|array|bool|int|string|null
    {
        return $this->flagDefaultValue;
    }

    /**
     * @param array|bool|string|numeric|null $flagDefaultValue
     * @return Activate
     */
    public function setFlagDefaultValue(float|array|bool|int|string|null $flagDefaultValue): static
    {
        $this->flagDefaultValue = $flagDefaultValue;
        return $this;
    }




    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
    {
        $apiKeys = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $this->getVisitorId(),
            FlagshipConstant::VARIATION_ID_API_ITEM => $this->getVariationId(),
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $this->getVariationGroupId(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->config->getEnvId(),
            FlagshipConstant::ANONYMOUS_ID => null,
            FlagshipConstant::QT_API_ITEM => round(microtime(true) * 1000) - $this->createdAt,
        ];

        if ($this->getVisitorId() && $this->getAnonymousId()) {
            $apiKeys[FlagshipConstant::VISITOR_ID_API_ITEM]  = $this->getVisitorId();
            $apiKeys[FlagshipConstant::ANONYMOUS_ID] = $this->getAnonymousId();
        }

        return $apiKeys;
    }


    /**
     * @inheritDoc
     */
    public function isReady(): bool
    {
        return parent::isReady() && $this->getVisitorId() && $this->getVariationGroupId();
    }


    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
