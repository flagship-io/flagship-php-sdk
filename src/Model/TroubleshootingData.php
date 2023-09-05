<?php

namespace Flagship\Model;

use DateTime;

class TroubleshootingData
{
    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @var DateTime
     */
    private $endDate;

    /**
     * @var numeric
     */
    private $traffic;

    /**
     * @var string
     */
    private $timezone;

    /**
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     * @return TroubleshootingData
     */
    public function setStartDate(DateTime $startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     * @return TroubleshootingData
     */
    public function setEndDate(DateTime $endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return float|int|string
     */
    public function getTraffic()
    {
        return $this->traffic;
    }

    /**
     * @param float|int|string $traffic
     * @return TroubleshootingData
     */
    public function setTraffic($traffic)
    {
        $this->traffic = $traffic;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return TroubleshootingData
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }
}
