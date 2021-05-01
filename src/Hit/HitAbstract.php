<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Traits\LogTrait;
use Flagship\Utils\LogManager;

/**
 * Class HitAbstract
 * @abstract
 * @package Flagship\Hit
 */
abstract class HitAbstract
{
    use LogTrait;

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
     */
    private $envId;
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     * @see \Flagship\Enum\HitType
     */
    protected $type;
    /**
     * @var int
     */
    protected $timeOut = FlagshipConstant::REQUEST_TIME_OUT;

    /**
     * @var LogManager
     */
    protected $logManager;

    /**
     * HitAbstract constructor.
     *
     * @param string $type : Hit type
     * <code>
     * Flagship\Enum\HitType::EVENT,
     * Flagship\Enum\HitType::ITEM,
     * Flagship\Enum\HitType::PAGEVIEW,
     * Flagship\Enum\HitType::TRANSACTION
     * </code>
     */
    public function __construct($type){
        $this->type = $type;
    }

    /**
     * @param mixed $value
     * @param string $itemName
     * @return bool
     */
    protected function isNoEmptyString($value, $itemName){
        if (empty($value) || !is_string($value)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'string'));
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value
     * @param string $itemName
     * @return bool
     */
    protected function isNumeric($value, $itemName){
        if (!is_numeric($value)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'numeric'));
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value
     * @param string $itemName
     * @return bool
     */
    protected function isInteger($value, $itemName){
        if (!is_int($value)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, $itemName, 'integer'));
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
     * @param string $visitorId
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
     * @param string $ds
     * @return HitAbstract
     */
    public function setDs($ds)
    {
        $this->ds = $ds;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnvId()
    {
        return $this->envId;
    }

    /**
     * Specifies customer environment id provided by Flagship
     *
     * @param string $envId
     * @return HitAbstract
     */
    public function setEnvId($envId)
    {
        $this->envId = $envId;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Specifies customer secure api key provided by Flagship.
     *
     * @param string $apiKey
     * @return HitAbstract
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Timeout for api request.
     *
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * Specify timeout for api request.
     *
     * @param int $timeOut
     * @return HitAbstract
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
        return $this;
    }

    /**
     * Hit Type
     * @see \Flagship\Enum\HitType
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Return an associative array of the class with Api parameters as keys
     * @return array
     */
    public function toArray(){
        return [
            FlagshipConstant::VISITOR_ID_API_ITEM=>$this->getVisitorId(),
            FlagshipConstant::DS_API_ITEM =>$this->getDs(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM =>$this->getEnvId(),
            FlagshipConstant::T_API_ITEM=>$this->getType()
        ];
    }

    /**
     * @return LogManager
     */
    public function getLogManager()
    {
        return $this->logManager;
    }

    /**
     * @param LogManager $logManager
     * @return HitAbstract
     */
    public function setLogManager($logManager)
    {
        $this->logManager = $logManager;
        return $this;
    }

    /**
     * Return true if all required attributes are given, otherwise return false
     * @return bool
     */
    public function isReady (){
        return $this->getVisitorId() && $this->getDs() && $this->getEnvId() && $this->getType();
    }

    /**
     * This function return the error message according to required attributes of class
     * @return string
     */
    abstract public function getErrorMessage();
}