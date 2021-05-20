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
    const ERROR_MESSAGE  = 'event category and event action are required';
    const CATEGORY_ERROR = "The category value must be either EventCategory::ACTION_TRACKING or EventCategory::ACTION_TRACKING";
    /**
     * @var string
     */
    private $category = null;

    /**
     * @var string
     */
    private $action = null;

    /**
     * @var string
     */
    private $eventLabel;

    /**
     * @var float
     */
    private $eventValue;

    /**
     * Event constructor.
     *
     * @param string $category : Action Tracking or User Engagement. @see Flagship\Enum\EventCategory
     * @param string $action   : Event name that will also serve as the KPI
     *                         that you will have inside your reporting.
     */
    public function __construct($category, $action)
    {
        parent::__construct(HitType::EVENT);
        $this->setCategory($category)
            ->setAction($action);
    }

    /**
     * Action Tracking or User Engagement.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Specify Action Tracking or User Engagement.
     * @see \Flagship\Enum\EventCategory
     * @param  string $category
     * @return Event
     */
    public function setCategory($category)
    {
        if ($category !== EventCategory::ACTION_TRACKING && $category !== EventCategory::USER_ENGAGEMENT) {
            $this->logError(
                $this->getConfig(),
                sprintf(self::CATEGORY_ERROR, 'category')
            );
            return $this;
        }
        $this->category = $category;
        return $this;
    }

    /**
     *  Event name.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Specify Event name that will also serve as the KPI
     * that you will have inside your reporting
     *
     * @param  string $action : Event name.
     * @return Event
     */
    public function setAction($action)
    {
        if (!$this->isNoEmptyString($action, 'action')) {
            return $this;
        }
        $this->action = $action;
        return $this;
    }

    /**
     * Additional description of event.
     *
     * @return string
     */
    public function getEventLabel()
    {
        return $this->eventLabel;
    }

    /**
     * Specify additional description of event.
     *
     * @param  string $eventLabel : event label.
     * @return Event
     */
    public function setEventLabel($eventLabel)
    {
        if (!$this->isNoEmptyString($eventLabel, 'eventLabel')) {
            return $this;
        }
        $this->eventLabel = $eventLabel;
        return $this;
    }

    /**
     * Monetary value associated with an event
     *
     * @return float
     */
    public function getEventValue()
    {
        return $this->eventValue;
    }

    /**
     * Specify the monetary value associated with an event
     *      (e.g. you earn 10 to 100 euros depending on the quality of lead generated).
     *      NOTE: this value must be non-negative.
     *
     * @param  float $eventValue : event value
     * @return Event
     */
    public function setEventValue($eventValue)
    {
        if (!$this->isNumeric($eventValue, 'eventValue')) {
            return $this;
        }
        $this->eventValue = $eventValue;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $arrayParent = parent::toArray();
        $arrayParent[FlagshipConstant::EVENT_CATEGORY_API_ITEM] = $this->getCategory();
        $arrayParent[FlagshipConstant::EVENT_ACTION_API_ITEM] = $this->getAction();

        if ($this->getEventLabel()) {
            $arrayParent[FlagshipConstant::EVENT_LABEL_API_ITEM] = $this->getEventLabel();
        }

        if ($this->getEventValue()) {
            $arrayParent[FlagshipConstant::EVENT_VALUE_API_ITEM] = $this->getEventValue();
        }

        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady()
    {
        return parent::isReady() && $this->getCategory() && $this->getAction();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}
