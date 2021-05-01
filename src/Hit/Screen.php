<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Screen extends HitAbstract
{
    /**
     * @var string
     */
    private $screenName;

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
        if (!is_string($screenName)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'screenName', 'string'));
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
}