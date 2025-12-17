<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type BucketingArray from Types
 */
class BucketingDTO
{
    private ?bool $panic = null;

    /** @var array<BucketingCampaignDTO>|null */
    private ?array $campaigns = null;

    private ?AccountSettingsDTO $accountSettings = null;

    public function getPanic(): ?bool
    {
        return $this->panic;
    }

    public function setPanic(?bool $panic): self
    {
        $this->panic = $panic;
        return $this;
    }

    /**
     * @return array<BucketingCampaignDTO>|null
     */
    public function getCampaigns(): ?array
    {
        return $this->campaigns;
    }

    /**
     * @param array<BucketingCampaignDTO>|null $campaigns
     */
    public function setCampaigns(?array $campaigns): self
    {
        $this->campaigns = $campaigns;
        return $this;
    }

    public function getAccountSettings(): ?AccountSettingsDTO
    {
        return $this->accountSettings;
    }

    public function setAccountSettings(?AccountSettingsDTO $accountSettings): self
    {
        $this->accountSettings = $accountSettings;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        if (isset($data[FlagshipField::FIELD_PANIC]) && is_bool($data[FlagshipField::FIELD_PANIC])) {
            $instance->setPanic($data[FlagshipField::FIELD_PANIC]);
        }

        if (isset($data[FlagshipField::FIELD_CAMPAIGNS]) && is_array($data[FlagshipField::FIELD_CAMPAIGNS])) {
            /** @var array<string, array<mixed>> $campaignsData */
            $campaignsData = $data[FlagshipField::FIELD_CAMPAIGNS];
            $campaigns = array_map(
                BucketingCampaignDTO::fromArray(...),
                $campaignsData
            );
            $instance->setCampaigns($campaigns);
        }

        if (isset($data[FlagshipField::ACCOUNT_SETTINGS]) && is_array($data[FlagshipField::ACCOUNT_SETTINGS])) {
            /** @var array<string, mixed> $accountSettingsData */
            $accountSettingsData = $data[FlagshipField::ACCOUNT_SETTINGS];
            $instance->setAccountSettings(
                AccountSettingsDTO::fromArray($accountSettingsData)
            );
        }

        return $instance;
    }

    /**
     * @return BucketingArray
     */
    public function toArray(): array
    {
        $result = [];

        if ($this->panic !== null) {
            $result[FlagshipField::FIELD_PANIC] = $this->panic;
        }

        if ($this->campaigns !== null) {
            $result[FlagshipField::FIELD_CAMPAIGNS] = array_map(
                fn(BucketingCampaignDTO $campaign) => $campaign->toArray(),
                $this->campaigns
            );
        }

        if ($this->accountSettings !== null) {
            $result[FlagshipField::ACCOUNT_SETTINGS] = $this->accountSettings->toArray();
        }

        return $result;
    }
}
