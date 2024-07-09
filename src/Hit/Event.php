<?php

namespace Flagship\Hit;

use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

/**
 * Flagship hit type Event
 *
 * @package Flagship\Hit
 */
class Event extends HitAbstract
{
    public const ERROR_MESSAGE  = 'event category and event action are required';
    public const CATEGORY_ERROR =
        "The category value must be either EventCategory::ACTION_TRACKING or EventCategory::ACTION_TRACKING";
    public const VALUE_FIELD_ERROR = 'value must be an integer and be >= 0';

    public static function getClassName(): string
    {
        return __CLASS__;
    }

    /**
     * @var EventCategory
     */
    private EventCategory $category;

    /**
     * @var string
     */
    private string $action;

    /**
     * @var string|null
     */
    private ?string $label = null;

    /**
     * @var float|null
     */
    private ?float $value = null;

    /**
     * Event constructor.
     *
     * @param EventCategory $category : Action Tracking or User Engagement.
     * @param string $action : Event name that will also serve as the KPI
     *                         that you will have inside your reporting.
     */
    public function __construct(EventCategory $category, string $action)
    {
        parent::__construct(HitType::EVENT);
        $this->category = $category;
        $this->action = $action;
    }

    /**
     * Action Tracking or User Engagement.
     *
     * @return EventCategory
     */
    public function getCategory(): EventCategory
    {
        return $this->category;
    }

    /**
     * Specify Action Tracking or User Engagement.
     * @param  EventCategory $category
     * @return Event
     */
    public function setCategory(EventCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     *  Event name.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Specify Event name that will also serve as the KPI
     * that you will have inside your reporting
     *
     * @param string $action : Event name.
     * @return Event
     */
    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Additional description of event.
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Specify additional description of event.
     *
     * @param ?string $label : event label.
     * @return Event
     */
    public function setLabel(?string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Monetary value associated with an event
     *
     * @return float|null
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * Specify the monetary value associated with an event
     *      (e.g. you earn 10 to 100 euros depending on the quality of lead generated).
     *      NOTE: this value must be non-negative.
     *
     * @param float|null $value : event value
     * @return Event
     */
    public function setValue(?float $value): static
    {
        if (is_numeric($value) && $value < 0) {
            $this->logError(
                $this->config,
                self::VALUE_FIELD_ERROR,
                [
                    FlagshipConstant::TAG => __FUNCTION__
                ]
            );
            return $this;
        }
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
    {
        $arrayParent = parent::toApiKeys();
        $arrayParent[FlagshipConstant::EVENT_CATEGORY_API_ITEM] = $this->getCategory();
        $arrayParent[FlagshipConstant::EVENT_ACTION_API_ITEM] = $this->getAction();

        if ($this->getLabel()) {
            $arrayParent[FlagshipConstant::EVENT_LABEL_API_ITEM] = $this->getLabel();
        }

        if ($this->getValue()) {
            $arrayParent[FlagshipConstant::EVENT_VALUE_API_ITEM] = $this->getValue();
        }

        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady(): bool
    {
        return parent::isReady() && $this->getCategory() && $this->getAction();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
