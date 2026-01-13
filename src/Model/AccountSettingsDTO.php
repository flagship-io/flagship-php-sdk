<?php

namespace Flagship\Model;

use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type AccountSettingsArray from Types
 */
class AccountSettingsDTO
{
    private ?bool $enabledXPC = null;

    private ?TroubleshootingDTO $troubleshooting = null;

    private ?bool $eaiCollectEnabled = null;

    private ?bool $eaiActivationEnabled = null;

    public function getEnabledXPC(): ?bool
    {
        return $this->enabledXPC;
    }

    public function setEnabledXPC(?bool $enabledXPC): self
    {
        $this->enabledXPC = $enabledXPC;
        return $this;
    }

    public function getTroubleshooting(): ?TroubleshootingDTO
    {
        return $this->troubleshooting;
    }

    public function setTroubleshooting(?TroubleshootingDTO $troubleshooting): self
    {
        $this->troubleshooting = $troubleshooting;
        return $this;
    }

    public function getEaiCollectEnabled(): ?bool
    {
        return $this->eaiCollectEnabled;
    }

    public function setEaiCollectEnabled(?bool $eaiCollectEnabled): self
    {
        $this->eaiCollectEnabled = $eaiCollectEnabled;
        return $this;
    }

    public function getEaiActivationEnabled(): ?bool
    {
        return $this->eaiActivationEnabled;
    }

    public function setEaiActivationEnabled(?bool $eaiActivationEnabled): self
    {
        $this->eaiActivationEnabled = $eaiActivationEnabled;
        return $this;
    }

    /**
     * Check if troubleshooting is enabled and valid
     * 
     * @return bool
     */
    public function isTroubleshootingEnabled(): bool
    {
        return $this->troubleshooting !== null;
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        if (isset($data[FlagshipField::ENABLED_XPC]) && is_bool($data[FlagshipField::ENABLED_XPC])) {
            $instance->setEnabledXPC($data[FlagshipField::ENABLED_XPC]);
        }

        if (isset($data[FlagshipField::TROUBLESHOOTING]) && is_array($data[FlagshipField::TROUBLESHOOTING])) {
            /** @var array<mixed> $troubleshootingData */
            $troubleshootingData = $data[FlagshipField::TROUBLESHOOTING];
            $instance->setTroubleshooting(
                TroubleshootingDTO::fromArray($troubleshootingData)
            );
        }

        if (isset($data[FlagshipField::EAI_COLLECT_ENABLED]) && is_bool($data[FlagshipField::EAI_COLLECT_ENABLED])) {
            $instance->setEaiCollectEnabled($data[FlagshipField::EAI_COLLECT_ENABLED]);
        }

        if (isset($data[FlagshipField::EAI_ACTIVATION_ENABLED]) && is_bool($data[FlagshipField::EAI_ACTIVATION_ENABLED])) {
            $instance->setEaiActivationEnabled($data[FlagshipField::EAI_ACTIVATION_ENABLED]);
        }

        return $instance;
    }

    /**
     * @return AccountSettingsArray
     */
    public function toArray(): array
    {
        $result = [];

        if ($this->enabledXPC !== null) {
            $result[FlagshipField::ENABLED_XPC] = $this->enabledXPC;
        }

        if ($this->troubleshooting !== null) {
            $result[FlagshipField::TROUBLESHOOTING] = $this->troubleshooting->toArray();
        }

        if ($this->eaiCollectEnabled !== null) {
            $result[FlagshipField::EAI_COLLECT_ENABLED] = $this->eaiCollectEnabled;
        }

        if ($this->eaiActivationEnabled !== null) {
            $result[FlagshipField::EAI_ACTIVATION_ENABLED] = $this->eaiActivationEnabled;
        }

        return $result;
    }
}
