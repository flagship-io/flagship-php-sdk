<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

/**
 * Flagship hit type Page
 *
 * @package Flagship\Hit
 */
class Page extends HitAbstract
{
    public const ERROR_MESSAGE = 'Page url is required';



    /**
     * @var string
     */
    private string $pageUrl;

    /**
     * Page constructor.
     *
     * @param string $pageUrl : Valid url of the page seen.
     */
    public function __construct(string $pageUrl)
    {
        parent::__construct(HitType::PAGE_VIEW);
        $this->setPageUrl($pageUrl);
    }

    /**
     * @return string
     */
    public function getPageUrl(): string
    {
        return $this->pageUrl;
    }

    /**
     * Specify Valid url of the page seen.
     *
     * @param string $pageUrl
     * @return Page
     */
    public function setPageUrl(string $pageUrl): self
    {
        $this->pageUrl = $pageUrl;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
    {
        $arrayParent = parent::toApiKeys();
        $arrayParent[FlagshipConstant::DL_API_ITEM] = $this->getPageUrl();
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady(): bool
    {
        return parent::isReady() && $this->getPageUrl();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
