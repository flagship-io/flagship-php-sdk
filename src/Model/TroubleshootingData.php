<?php

namespace Flagship\Model;

use DateTime;

class TroubleshootingData
{
    /**
     * @var DateTime
     */
    private DateTime $startDate;

    /**
     * @var DateTime
     */
    private DateTime $endDate;

    /**
     * @var int|float $traffic
     */
    private int|float $traffic;

    /**
     * @var string
     */
    private string $timezone;

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     * @return TroubleshootingData
     */
    public function setStartDate(DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     * @return TroubleshootingData
     */
    public function setEndDate(DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getTraffic(): float|int
    {
        return $this->traffic;
    }

    /**
     * @param float|int $traffic
     * @return TroubleshootingData
     */
    public function setTraffic(float|int $traffic): self
    {
        $this->traffic = $traffic;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return TroubleshootingData
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }
}
