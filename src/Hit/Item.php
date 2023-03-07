<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

/**
 * Flagship hit type Item
 *
 * @package Flagship\Hit
 */
class Item extends HitAbstract
{
    const ERROR_MESSAGE = 'Transaction Id, Item name and item code are required';

    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * @var string
     */
    private $transactionId = null;

    /**
     * @var string
     */
    private $productName = null;

    /**
     * @var string
     */
    private $productSku = null;

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
     *
     * @param string $transactionId : Transaction unique identifier.
     * @param string $productName      : Name of the item product.
     * @param string $productSku      : The SKU or item code.
     */
    public function __construct($transactionId, $productName, $productSku)
    {
        parent::__construct(HitType::ITEM);

        $this->setTransactionId($transactionId);
        $this->setProductName($productName);
        $this->setProductSku($productSku);
    }

    /**
     * Transaction unique identifier.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Specify transaction unique identifier.
     *
     * @param  string $transactionId : Transaction unique identifier.
     * @return Item
     */
    public function setTransactionId($transactionId)
    {
        if (!$this->isNoEmptyString($transactionId, 'transactionId')) {
            return $this;
        }
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Name of the item product.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Specify name of the item product.
     *
     * @param  string $productName : Name of the item product.
     * @return Item
     */
    public function setProductName($productName)
    {
        if (!$this->isNoEmptyString($productName, 'productName')) {
            return $this;
        }
        $this->productName = $productName;
        return $this;
    }

    /**
     * The SKU or item code.
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->productSku;
    }

    /**
     * Specify the SKU or item code.
     *
     * @param  string $productSku
     * @return Item
     */
    public function setProductSku($productSku)
    {
        if (!$this->isNoEmptyString($productSku, 'productSku')) {
            return $this;
        }
        $this->productSku = $productSku;
        return $this;
    }

    /**
     * Price for a single item/unit.
     *
     * @return float
     */
    public function getItemPrice()
    {
        return $this->itemPrice;
    }

    /**
     * Specify the price for a single item
     *
     * @param  float $itemPrice
     * @return Item
     */
    public function setItemPrice($itemPrice)
    {
        if (!$this->isNumeric($itemPrice, 'itemPrice')) {
            return $this;
        }
        $this->itemPrice = $itemPrice;
        return $this;
    }

    /**
     * Number of items purchased.
     *
     * @return int
     */
    public function getItemQuantity()
    {
        return $this->itemQuantity;
    }

    /**
     * Specify the number of items purchased.
     *
     * @param  int $itemQuantity
     * @return Item
     */
    public function setItemQuantity($itemQuantity)
    {
        if (!$this->isInteger($itemQuantity, "itemQuantity")) {
            return $this;
        }
        $this->itemQuantity = $itemQuantity;
        return $this;
    }

    /**
     * Category that the item belongs to
     *
     * @return string
     */
    public function getItemCategory()
    {
        return $this->itemCategory;
    }

    /**
     * Specify the category that the item belongs to
     *
     * @param  string $itemCategory
     * @return Item
     */
    public function setItemCategory($itemCategory)
    {
        if (!$this->isNoEmptyString($itemCategory, "itemCategory")) {
            return $this;
        }
        $this->itemCategory = $itemCategory;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys()
    {
        $arrayParent = parent::toApiKeys();
        $arrayParent[FlagshipConstant::TID_API_ITEM] = $this->getTransactionId();
        $arrayParent[FlagshipConstant::IN_API_ITEM] = $this->getProductName();
        $arrayParent[FlagshipConstant::IC_API_ITEM] = $this->getProductSku();

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
        return parent::isReady() && $this->getTransactionId() && $this->getProductName() && $this->getProductSku();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}
