<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

/**
 * Flagship hit type Page
 * @package Flagship\Hit
 */
class Page extends HitAbstract
{
    const ERROR_MESSAGE = 'Page url is required';

    /**
     * @var string
     */
    private $pageUrl = null;

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
     * Specify Valid url of the page seen.
     *
     * @param string $pageUrl
     * @return Page
     */
    public function setPageUrl($pageUrl)
    {
        if (!$this->isNoEmptyString($pageUrl,'pageUrl')){
            return $this;
        }
        $this->pageUrl = $pageUrl;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $arrayParent = parent::toArray();
        $arrayParent[FlagshipConstant::DL_API_ITEM] = $this->getPageUrl();
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady()
    {
        return parent::isReady() && $this->getPageUrl();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}