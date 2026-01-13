<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

/**
 * Flagship hit type Screen
 *
 * @package Flagship\Hit
 */
class Screen extends HitAbstract
{
    public const ERROR_MESSAGE = 'Screen name is required';



    /**
     * @var string
     */
    private string $screenName;

    /**
     * Screen constructor.
     *
     * @param string $screenName : Name of the interface seen.
     */
    public function __construct(string $screenName)
    {
        parent::__construct(HitType::SCREEN_VIEW);

        $this->setScreenName($screenName);
    }

    /**
     * Name of the interface seen.
     *
     * @return string
     */
    public function getScreenName(): string
    {
        return $this->screenName;
    }

    /**
     * Specify Name of the interface seen.
     *
     * @param string $screenName : Interface seen.
     * @return Screen
     */
    public function setScreenName(string $screenName): self
    {
        $this->screenName = $screenName;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
    {
        $arrayParent = parent::toApiKeys();
        $arrayParent[FlagshipConstant::DL_API_ITEM] = $this->getScreenName();
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady(): bool
    {
        return parent::isReady() && $this->getScreenName();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
