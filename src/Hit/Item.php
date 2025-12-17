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
    public const ERROR_MESSAGE = 'Transaction Id, Item name and item code are required';



    /**
     * @var string
     */
    private string $transactionId;

    /**
     * @var string
     */
    private string $productName;

    /**
     * @var string
     */
    private string $productSku;

    /**
     * @var float|null
     */
    private ?float $itemPrice = null;

    /**
     * @var integer|null
     */
    private ?int $itemQuantity = null;

    /**
     * @var string|null
     */
    private ?string $itemCategory = null;

    /**
     * Item constructor.
     *
     * @param string $transactionId : Transaction unique identifier.
     * @param string $productName      : Name of the item product.
     * @param string $productSku      : The SKU or item code.
     */
    public function __construct(string $transactionId, string $productName, string $productSku)
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
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Specify transaction unique identifier.
     *
     * @param string $transactionId : Transaction unique identifier.
     * @return Item
     */
    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Name of the item product.
     *
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * Specify name of the item product.
     *
     * @param string $productName : Name of the item product.
     * @return Item
     */
    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    /**
     * The SKU or item code.
     *
     * @return string
     */
    public function getProductSku(): string
    {
        return $this->productSku;
    }

    /**
     * Specify the SKU or item code.
     *
     * @param string $productSku
     * @return Item
     */
    public function setProductSku(string $productSku): self
    {
        $this->productSku = $productSku;
        return $this;
    }

    /**
     * Price for a single item/unit.
     *
     * @return float|null
     */
    public function getItemPrice(): ?float
    {
        return $this->itemPrice;
    }

    /**
     * Specify the price for a single item
     *
     * @param ?float $itemPrice
     * @return Item
     */
    public function setItemPrice(?float $itemPrice): self
    {
        $this->itemPrice = $itemPrice;
        return $this;
    }

    /**
     * Number of items purchased.
     *
     * @return int|null
     */
    public function getItemQuantity(): ?int
    {
        return $this->itemQuantity;
    }

    /**
     * Specify the number of items purchased.
     *
     * @param ?int $itemQuantity
     * @return Item
     */
    public function setItemQuantity(?int $itemQuantity): self
    {
        $this->itemQuantity = $itemQuantity;
        return $this;
    }

    /**
     * Category that the item belongs to
     *
     * @return string|null
     */
    public function getItemCategory(): ?string
    {
        return $this->itemCategory;
    }

    /**
     * Specify the category that the item belongs to
     *
     * @param ?string $itemCategory
     * @return Item
     */
    public function setItemCategory(?string $itemCategory): self
    {
        $this->itemCategory = $itemCategory;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
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
    public function isReady(): bool
    {
        return parent::isReady() && $this->getTransactionId() && $this->getProductName() && $this->getProductSku();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
