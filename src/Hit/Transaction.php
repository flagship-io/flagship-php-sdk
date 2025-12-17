<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

/**
 * Flagship hit type Transaction
 *
 * @package Flagship\Hit
 */
class Transaction extends HitAbstract
{
    public const CURRENCY_ERROR = "'%s' must be a string and have exactly 3 letters";
    public const ERROR_MESSAGE  = 'Transaction Id and Transaction affiliation are required';



    /**
     * @var string
     */
    private string $transactionId;
    /**
     * @var string
     */
    private string $affiliation;
    /**
     * @var float|null
     */
    private ?float $taxes = null;
    /**
     * @var string|null
     */
    private ?string $currency = null;
    /**
     * @var string|null
     */
    private ?string $couponCode = null;
    /**
     * @var int|null
     */
    private ?int $itemCount = null;
    /**
     * @var string|null
     */
    private ?string $shippingMethod = null;
    /**
     * @var string|null
     */
    private ?string $paymentMethod = null;
    /**
     * @var float|null
     */
    private ?float $totalRevenue = null;
    /**
     * @var float|null
     */
    private ?float $shippingCosts = null;

    /**
     * Transaction constructor.
     *
     * @param string $transactionId : Transaction unique identifier..
     * @param string $affiliation   : Transaction affiliation.
     */
    public function __construct(string $transactionId, string $affiliation)
    {
        parent::__construct(HitType::TRANSACTION);
        $this->setTransactionId($transactionId);
        $this->setAffiliation($affiliation);
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
     * Set Transaction unique identifier.
     *
     * @param string $transactionId
     * @return Transaction
     */
    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Transaction affiliation
     *
     * @return string
     */
    public function getAffiliation(): string
    {
        return $this->affiliation;
    }

    /**
     * set transaction affiliation
     *
     * @param string $affiliation
     * @return Transaction
     */
    public function setAffiliation(string $affiliation): self
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    /**
     * Total amount of taxes
     *
     * @return float|null
     */
    public function getTaxes(): ?float
    {
        return $this->taxes;
    }

    /**
     * Specify the total amount of taxes
     *
     * @param ?float $taxes
     * @return Transaction
     */
    public function setTaxes(?float $taxes): self
    {
        $this->taxes = $taxes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * Specify the currency of transaction.
     *     NOTE: This value should be a valid ISO 4217 currency code.
     *
     * @param ?string $currency
     * @return Transaction
     */
    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Coupon code used by the customer
     *
     * @return string|null
     */
    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    /**
     * Specify the coupon code used by the customer
     *
     * @param ?string $couponCode
     * @return Transaction
     */
    public function setCouponCode(?string $couponCode): self
    {
        $this->couponCode = $couponCode;
        return $this;
    }

    /**
     * The number of items in the transaction
     *
     * @return int|null
     */
    public function getItemCount(): ?int
    {
        return $this->itemCount;
    }

    /**
     * Specify the number of items in the transaction.
     *
     * @param ?integer $itemsCount
     * @return Transaction
     */
    public function setItemCount(?int $itemsCount): self
    {
        $this->itemCount = $itemsCount;
        return $this;
    }

    /**
     * The shipping method.
     *
     * @return string|null
     */
    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    /**
     * Specify the shipping method.
     *
     * @param ?string $shippingMethod
     * @return Transaction
     */
    public function setShippingMethod(?string $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * Payment method
     *
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * Specify the payment method used
     *
     * @param ?string $paymentMethod
     * @return Transaction
     */
    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * Total revenue associated with the transaction
     *
     * @return float|null
     */
    public function getTotalRevenue(): ?float
    {
        return $this->totalRevenue;
    }

    /**
     * Specify the total revenue associated with the transaction.
     *     NOTE: This value should include any shipping and/or tax amounts.
     *
     * @param ?float $totalRevenue
     * @return Transaction
     */
    public function setTotalRevenue(?float $totalRevenue): self
    {
        $this->totalRevenue = $totalRevenue;
        return $this;
    }

    /**
     * Total shipping cost of the transaction
     *
     * @return float|null
     */
    public function getShippingCosts(): ?float
    {
        return $this->shippingCosts;
    }

    /**
     * Specify the total shipping cost of the transaction\
     *
     * @param ?float $shippingCosts
     * @return Transaction
     */
    public function setShippingCosts(?float $shippingCosts): self
    {
        $this->shippingCosts = $shippingCosts;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
    {
        $arrayParent = parent::toApiKeys();
        $arrayParent[FlagshipConstant::TID_API_ITEM] = $this->getTransactionId();
        $arrayParent[FlagshipConstant::TA_API_ITEM] = $this->getAffiliation();

        if ($this->getTaxes()) {
            $arrayParent[FlagshipConstant::TT_API_ITEM] = $this->getTaxes();
        }

        if ($this->getCurrency()) {
            $arrayParent[FlagshipConstant::TC_API_ITEM] = $this->getCurrency();
        }

        if ($this->getCouponCode()) {
            $arrayParent[FlagshipConstant::TCC_API_ITEM] = $this->getCouponCode();
        }

        if ($this->getItemCount()) {
            $arrayParent[FlagshipConstant::ICN_API_ITEM] = $this->getItemCount();
        }

        if ($this->getShippingMethod()) {
            $arrayParent[FlagshipConstant::SM_API_ITEM] = $this->getShippingMethod();
        }

        if ($this->getPaymentMethod()) {
            $arrayParent[FlagshipConstant::PM_API_ITEM] = $this->getPaymentMethod();
        }

        if ($this->getTotalRevenue()) {
            $arrayParent[FlagshipConstant::TR_API_ITEM] = $this->getTotalRevenue();
        }

        if ($this->getShippingCosts()) {
            $arrayParent[FlagshipConstant::TS_API_ITEM] = $this->getShippingCosts();
        }

        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady(): bool
    {
        return parent::isReady() && $this->getTransactionId() && $this->getAffiliation();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
