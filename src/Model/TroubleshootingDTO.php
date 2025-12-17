<?php

namespace Flagship\Model;

use DateTime;
use Exception;
use Flagship\Enum\FlagshipField;

/**
 * @phpstan-import-type TroubleshootingArray from Types
 */
class TroubleshootingDTO
{
    private string $startDate;

    private string $endDate;

    private float $traffic;

    private string $timezone;

    public function __construct(
        string $startDate,
        string $endDate,
        float $traffic,
        string $timezone
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->traffic = $traffic;
        $this->timezone = $timezone;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function setStartDate(string $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * Get start date as DateTime object
     * 
     * @return DateTime|null
     */
    public function getStartDateAsDateTime(): ?DateTime
    {
        try {
            return new DateTime($this->startDate);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function setEndDate(string $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Get end date as DateTime object
     * 
     * @return DateTime|null
     */
    public function getEndDateAsDateTime(): ?DateTime
    {
        try {
            return new DateTime($this->endDate);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTraffic(): float
    {
        return $this->traffic;
    }

    public function setTraffic(float $traffic): self
    {
        $this->traffic = $traffic;
        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $startDate = $data[FlagshipField::START_DATE] ?? '';
        $endDate = $data[FlagshipField::END_DATE] ?? '';
        $traffic = $data[FlagshipField::TRAFFIC] ?? 0;
        $timezone = $data[FlagshipField::TIMEZONE] ?? '';

        return new self(
            is_string($startDate) ? $startDate : '',
            is_string($endDate) ? $endDate : '',
            is_numeric($traffic) ? (float)$traffic : 0.0,
            is_string($timezone) ? $timezone : ''
        );
    }

    /**
     * @return TroubleshootingArray
     */
    public function toArray(): array
    {
        return [
            FlagshipField::START_DATE => $this->startDate,
            FlagshipField::END_DATE => $this->endDate,
            FlagshipField::TRAFFIC => $this->traffic,
            FlagshipField::TIMEZONE => $this->timezone,
        ];
    }

    /**
     * Convert to legacy TroubleshootingData format
     * 
     * @return TroubleshootingData|null
     */
    public function toTroubleshootingData(): ?TroubleshootingData
    {
        $startDateTime = $this->getStartDateAsDateTime();
        $endDateTime = $this->getEndDateAsDateTime();

        if (!$startDateTime || !$endDateTime) {
            return null;
        }

        $data = new TroubleshootingData();
        $data->setStartDate($startDateTime)
            ->setEndDate($endDateTime)
            ->setTraffic($this->traffic)
            ->setTimezone($this->timezone);

        return $data;
    }
}
