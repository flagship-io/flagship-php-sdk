<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Item extends HitAbstract
{
    const ERROR_MESSAGE = 'Transaction Id, Item name and item code are required';
    /**
     * @var string
     */
    private $transactionId = null;

    /**
     * @var string
     */
    private $itemName = null;

    /**
     * @var string
     */
    private $itemCode = null;

    /**
     * @var float
     */
    private $itemPrice;

    /**
     * @var integer
     */
    private $itemQuantity;

    /**
     * @var string
     */
    private $itemCategory;

    /**
     * Item constructor.
     * @param $transactionId
     * @param $itemName
     * @param $itemCode
     */
    public function __construct($transactionId, $itemName, $itemCode)
    {
        parent::__construct(HitType::ITEM);

        $this->setTransactionId($transactionId);
        $this->setItemName($itemName);
        $this->setItemCode($itemCode);
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     * @return Item
     */
    public function setTransactionId($transactionId)
    {
        if (!$this->isNoEmptyString($transactionId,'transactionId')) {
            return $this;
        }
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    /**
     * @param string $itemName
     * @return Item
     */
    public function setItemName($itemName)
    {
        if (!$this->isNoEmptyString($itemName,'itemName')) {
            return $this;
        }
        $this->itemName = $itemName;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemCode()
    {
        return $this->itemCode;
    }

    /**
     * @param string $itemCode
     * @return Item
     */
    public function setItemCode($itemCode)
    {
        if (!$this->isNoEmptyString($itemCode,'itemCode')) {
            return $this;
        }
        $this->itemCode = $itemCode;
        return $this;
    }

    /**
     * @return float
     */
    public function getItemPrice()
    {
        return $this->itemPrice;
    }

    /**
     * @param float $itemPrice
     * @return Item
     */
    public function setItemPrice($itemPrice)
    {
        if (!$this->isNumeric($itemPrice,'itemPrice')){
            return $this;
        }
        $this->itemPrice = $itemPrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemQuantity()
    {
        return $this->itemQuantity;
    }

    /**
     * @param int $itemQuantity
     * @return Item
     */
    public function setItemQuantity($itemQuantity)
    {
        if (!$this->isInteger($itemQuantity, "itemQuantity")){
            return $this;
        }
        $this->itemQuantity = $itemQuantity;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemCategory()
    {
        return $this->itemCategory;
    }

    /**
     * @param string $itemCategory
     * @return Item
     */
    public function setItemCategory($itemCategory)
    {
        if (!$this->isNoEmptyString($itemCategory,"itemCategory")){
            return $this;
        }
        $this->itemCategory = $itemCategory;
        return $this;
    }

    public function toArray()
    {
        $arrayParent = parent::toArray();
        $arrayParent[FlagshipConstant::TID_API_ITEM]= $this->getTransactionId();
        $arrayParent[FlagshipConstant::IN_API_ITEM]= $this->getItemName();
        $arrayParent[FlagshipConstant::IC_API_ITEM]= $this->getItemCode();

        if ($this->getItemPrice()) {
            $arrayParent[FlagshipConstant::IP_API_ITEM] = $this->getItemPrice();
        }

        if ($this->getItemQuantity()) {
            $arrayParent[FlagshipConstant::IQ_API_ITEM] = $this->getItemQuantity();
        }

        if ($this->getItemCategory()) {
            $arrayParent[FlagshipConstant::IV_API_ITEM] = $this->getItemCategory();
        }
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady()
    {
        return parent::isReady() && $this->getTransactionId() && $this->getItemName() && $this->getItemCode();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}