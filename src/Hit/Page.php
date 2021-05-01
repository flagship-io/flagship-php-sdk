<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Page extends HitAbstract
{
    private $pageUrl;

    /**
     * Page constructor.
     * @param string $pageUrl : Valid url of the page seen.
     */
    public function __construct($pageUrl)
    {
        parent::__construct(HitType::PAGE_VIEW);
        $this->setPageUrl($pageUrl);
    }

    /**
     * @return string
     */
    public function getPageUrl()
    {
        return $this->pageUrl;
    }

    /**
     * @param string $pageUrl
     * @return Page
     */
    public function setPageUrl($pageUrl)
    {
        $this->pageUrl = $pageUrl; // Todo: check url
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(){
        $arrayParent = parent::toArray();
        $arrayParent[FlagshipConstant::DL_API_ITEM]= $this->getPageUrl();
        return $arrayParent;
    }
}