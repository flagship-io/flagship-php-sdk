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
    private $visitorId;
    private $ds;
    private $envId;
    private $apiKey;
    protected $type;

    /**
     * @var LogManager
     */
    protected $logManager;

    public function __construct($type){
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * @param mixed $visitorId
     * @return HitAbstract
     */
    public function setVisitorId($visitorId)
    {
        $this->visitorId = $visitorId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDs()
    {
        return $this->ds;
    }

    /**
     * @param mixed $ds
     * @return HitAbstract
     */
    public function setDs($ds)
    {
        $this->ds = $ds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnvId()
    {
        return $this->envId;
    }

    /**
     * @param mixed $envId
     * @return HitAbstract
     */
    public function setEnvId($envId)
    {
        $this->envId = $envId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     * @return HitAbstract
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


    /**
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
}