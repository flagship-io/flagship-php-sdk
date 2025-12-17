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
     * @var array<mixed>|scalar|null
     */
    private string|array|bool|int|float|null $flagValue = null;

    /**
     * @var ?array<string, scalar>
     */
    private ?array $visitorContext = null;

    /**
     * @var FSFlagMetadataInterface
     */
    private FSFlagMetadataInterface $flagMetadata;

    /**
     * @var  scalar|array<mixed>|null
     */
    private string|array|bool|int|float|null $flagDefaultValue = null;





    /**
     * @param string $variationGroupId
     * @param string $variationId
     */
    public function __construct(
        string $variationGroupId,
        string $variationId,
        string $flagKey,
        FSFlagMetadataInterface $flagMetadata
    ) {
        parent::__construct(HitType::ACTIVATE);
        $this->variationGroupId = $variationGroupId;
        $this->variationId = $variationId;
        $this->flagKey = $flagKey;
        $this->flagMetadata = $flagMetadata;
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
    public function setVariationGroupId(string $variationGroupId): self
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
    public function setVariationId(string $variationId): self
    {
        $this->variationId = $variationId;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getFlagKey(): ?string
    {
        return $this->flagKey;
    }

    /**
     * @param string $flagKey
     * @return Activate
     */
    public function setFlagKey(string $flagKey): self
    {
        $this->flagKey = $flagKey;
        return $this;
    }

    /**
     * @return  array<mixed>|scalar|null
     */
    public function getFlagValue(): float|array|bool|int|string|null
    {
        return $this->flagValue;
    }

    /**
     * @param array<mixed>|scalar|null $flagValue
     * @return Activate
     */
    public function setFlagValue(float|array|bool|int|string|null $flagValue): self
    {
        $this->flagValue = $flagValue;
        return $this;
    }

    /**
     * @return ?array<string, scalar>
     */
    public function getVisitorContext(): ?array
    {
        return $this->visitorContext;
    }

    /**
     * @param ?array<string, scalar> $visitorContext
     * @return Activate
     */
    public function setVisitorContext(?array $visitorContext): self
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
    public function setFlagMetadata(FSFlagMetadataInterface $flagMetadata): self
    {
        $this->flagMetadata = $flagMetadata;
        return $this;
    }

    /**
     * @return array<mixed>|scalar|null
     */
    public function getFlagDefaultValue(): float|array|bool|int|string|null
    {
        return $this->flagDefaultValue;
    }

    /**
     * @param array<mixed>|scalar|null $flagDefaultValue
     * @return Activate
     */
    public function setFlagDefaultValue(float|array|bool|int|string|null $flagDefaultValue): self
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
            FlagshipConstant::VISITOR_ID_API_ITEM         => $this->getVisitorId(),
            FlagshipConstant::VARIATION_ID_API_ITEM       => $this->getVariationId(),
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $this->getVariationGroupId(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM    => $this->config?->getEnvId() ?? '',
            FlagshipConstant::ANONYMOUS_ID                => null,
            FlagshipConstant::QT_API_ITEM                 => $this->getNow() - $this->createdAt,
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
