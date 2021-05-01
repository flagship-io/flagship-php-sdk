<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Screen extends HitAbstract
{
    const ERROR_MESSAGE = 'Screen name is required';
    /**
     * @var string
     */
    private $screenName = null;

    /**
     * Screen constructor.
     * @param string $screenName : Name of the interface seen.
     */
    public function __construct($screenName)
    {
        parent::__construct(HitType::SCREEN_VIEW);

        $this->setScreenName($screenName);
    }

    /**
     * @return string
     */
    public function getScreenName()
    {
        return $this->screenName;
    }

    /**
     * @param string $screenName
     * @return Screen
     */
    public function setScreenName($screenName)
    {
        if (!$this->isNoEmptyString($screenName,'screenName')){
            return $this;
        }
        $this->screenName = $screenName;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(){
        $arrayParent = parent::toArray();
        $arrayParent[FlagshipConstant::DL_API_ITEM]= $this->getScreenName();
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady()
    {
        return parent::isReady() && $this->getScreenName();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}