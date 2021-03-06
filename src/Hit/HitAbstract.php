<?php

namespace Flagship\Hit;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Traits\BuildApiTrait;
use Flagship\Traits\LogTrait;

/**
 * Class HitAbstract
 *
 * @abstract
 * @package  Flagship\Hit
 */
abstract class HitAbstract
{
    use LogTrait;
    use BuildApiTrait;

    /**
     * @var string
     */
    private $visitorId;
    /**
     * @var string
     */
    private $ds;

    /**
     * @var string
     * @see \Flagship\Enum\HitType
     */
    protected $type;

    /**
     * @var FlagshipConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $anonymousId;

    /**
     * The User IP address
     * @var string
     */
    protected $userIP;

    /**
     * @var string
     */
    protected $screenResolution;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var numeric
     */
    protected $sessionNumber;

    /**
     * HitAbstract constructor.
     *
     * @param string $type : Hit type
     *                     <code>
     *                     Flagship\Enum\HitType::EVENT,
     *                     Flagship\Enum\HitType::ITEM,
     *                     Flagship\Enum\HitType::PAGEVIEW,
     *                     Flagship\Enum\HitType::TRANSACTION
     *                     </code>
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param  mixed  $value
     * @param  string $itemName
     * @return bool
     */
    protected function isNoEmptyString($value, $itemName)
    {
        if (empty($value) || !is_string($value)) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'string')
            );
            return false;
        }
        return true;
    }

    /**
     * @param  mixed  $value
     * @param  string $itemName
     * @return bool
     */
    protected function isNumeric($value, $itemName)
    {
        if (!is_numeric($value)) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'numeric')
            );
            return false;
        }
        return true;
    }

    /**
     * @param  mixed  $value
     * @param  string $itemName
     * @return bool
     */
    protected function isInteger($value, $itemName)
    {
        if (!is_int($value)) {
            $this->logError(
                $this->config,
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'integer')
            );
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * Specifies visitor unique identifier provided by developer at visitor creation
     *
     * @param  string $visitorId
     * @return HitAbstract
     */
    public function setVisitorId($visitorId)
    {
        $this->visitorId = $visitorId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDs()
    {
        return $this->ds;
    }

    /**
     * @param  string $ds
     * @return HitAbstract
     */
    public function setDs($ds)
    {
        $this->ds = $ds;
        return $this;
    }

    /**
     * Hit Type
     *
     * @see    \Flagship\Enum\HitType
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @return HitAbstract
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }

    /**
     * @param string $anonymousId
     * @return HitAbstract
     */
    public function setAnonymousId($anonymousId)
    {
        $this->anonymousId = $anonymousId;
        return $this;
    }

    /**
     * The User IP address
     * @return string
     */
    public function getUserIP()
    {
        return $this->userIP;
    }

    /**
     * Define the User IP address
     * @param string $userIP
     * @return HitAbstract
     */
    public function setUserIP($userIP)
    {
        $this->userIP = $userIP;
        return $this;
    }

    /**
     * Screen Resolution.
     * @return string
     */
    public function getScreenResolution()
    {
        return $this->screenResolution;
    }

    /**
     * Screen Resolution
     * @param string $screenResolution
     * @return HitAbstract
     */
    public function setScreenResolution($screenResolution)
    {
        $this->screenResolution = $screenResolution;
        return $this;
    }

    /**
     * User language
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Define User language
     * @param string $locale
     * @return HitAbstract
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Session number. Number of sessions the current visitor has logged, including the current session
     * @return float|int|string
     */
    public function getSessionNumber()
    {
        return $this->sessionNumber;
    }

    /**
     * Define Session number. Number of sessions the current visitor has logged, including the current session
     * @param float|int|string $sessionNumber
     * @return HitAbstract
     */
    public function setSessionNumber($sessionNumber)
    {
        $this->sessionNumber = $sessionNumber;
        return $this;
    }




    /**
     * Return an associative array of the class with Api parameters as keys
     *
     * @return array
     */
    public function toArray()
    {
        $data = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $this->getVisitorId(),
            FlagshipConstant::DS_API_ITEM => $this->getDs(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->getConfig()->getEnvId(),
            FlagshipConstant::T_API_ITEM => $this->getType(),
            FlagshipConstant::USER_IP_API_ITEM => $this->getUserIP(),
            FlagshipConstant::SCREEN_RESOLUTION_API_ITEM => $this->getScreenResolution(),
            FlagshipConstant::USER_LANGUAGE => $this->getLocale(),
            FlagshipConstant::SESSION_NUMBER => $this->getSessionNumber()
        ];

        if ($this->visitorId && $this->anonymousId) {
            $data[FlagshipConstant::VISITOR_ID_API_ITEM] = $this->anonymousId;
            $data[FlagshipConstant::CUSTOMER_UID] = $this->visitorId;
        } else {
            $data[FlagshipConstant::VISITOR_ID_API_ITEM] = $this->visitorId ?: $this->anonymousId;
            $data[FlagshipConstant::CUSTOMER_UID] = null;
        }
        return $data;
    }

    /**
     * Return true if all required attributes are given, otherwise return false
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->getVisitorId() && $this->getDs() && $this->getConfig() &&
            $this->getConfig()->getEnvId() && $this->getType();
    }

    /**
     * This function return the error message according to required attributes of class
     *
     * @return string
     */
    abstract public function getErrorMessage();
}
