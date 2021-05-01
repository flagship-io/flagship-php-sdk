<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Event extends HitAbstract
{
    const ERROR_MESSAGE = 'event category and event action are required';
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
    private $label;

    /**
     * @var float
     */
    private $value;

    /**
     * Event constructor.
     * @param string $category
     * @param string $action
     */
    public function __construct($category, $action)
    {
        parent::__construct(HitType::EVENT);
        $this->setCategory($category)
            ->setAction($action);
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return Event
     */
    public function setCategory($category)
    {
        if (!$this->isNoEmptyString($category, 'category')) {
            return $this;
        }
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
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
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Event
     */
    public function setLabel($label)
    {
        if (!$this->isNoEmptyString($label, 'label')) {
            return $this;
        }
        $this->label = $label;
        return $this;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return Event
     */
    public function setValue($value)
    {
        if (!$this->isNumeric($value, 'value')) {
            return $this;
        }
        $this->value = $value;
        return $this;
    }

    public function toArray()
    {
        $arrayParent = parent::toArray();
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