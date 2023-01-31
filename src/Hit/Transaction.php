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
    const CURRENCY_ERROR = "'%s' must be a string and have exactly 3 letters";
    const ERROR_MESSAGE  = 'Transaction Id and Transaction affiliation are required';

    /**
     * @return string
     */
    public static function getClassName(){
        return __CLASS__;
    }

    /**
     * @var string
     */
    private $transactionId;
    /**
     * @var string
     */
    private $affiliation;
    /**
     * @var float
     */
    private $taxes;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var string
     */
    private $couponCode;
    /**
     * @var int
     */
    private $itemCount;
    /**
     * @var string
     */
    private $shippingMethod;
    /**
     * @var string
     */
    private $paymentMethod;
    /**
     * @var float
     */
    private $totalRevenue;
    /**
     * @var float
     */
    private $shippingCosts;

    /**
     * Transaction constructor.
     *
     * @param string $transactionId : Transaction unique identifier..
     * @param string $affiliation   : Transaction affiliation.
     */
    public function __construct($transactionId, $affiliation)
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
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set Transaction unique identifier.
     *
     * @param string $transactionId
     * @return Transaction
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
     * Transaction affiliation
     *
     * @return string
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * set transaction affiliation
     *
     * @param string $affiliation
     * @return Transaction
     */
    public function setAffiliation($affiliation)
    {
        if (
            !$this->isNoEmptyString(
                $affiliation,
                'affiliation'
            )
        ) {
            return $this;
        }
        $this->affiliation = $affiliation;
        return $this;
    }

    /**
     * Total amount of taxes
     *
     * @return float
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * Specify the total amount of taxes
     *
     * @param float $taxes
     * @return Transaction
     */
    public function setTaxes($taxes)
    {
        if (!$this->isNumeric($taxes, 'taxes')) {
            return $this;
        }
        $this->taxes = $taxes;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Specify the currency of transaction.
     *     NOTE: This value should be a valid ISO 4217 currency code.
     *
     * @param string $currency
     * @return Transaction
     */
    public function setCurrency($currency)
    {
        if (!is_string($currency) || strlen($currency) != 3) {
            $this->logError(
                $this->getConfig(),
                sprintf(self::CURRENCY_ERROR, 'currency')
            );
            return $this;
        }
        $this->currency = strtoupper($currency);
        return $this;
    }

    /**
     * Coupon code used by the customer
     *
     * @return string
     */
    public function getCouponCode()
    {
        return $this->couponCode;
    }

    /**
     * Specify the coupon code used by the customer
     *
     * @param string $couponCode
     * @return Transaction
     */
    public function setCouponCode($couponCode)
    {
        if (!$this->isNoEmptyString($couponCode, 'couponCode')) {
            return $this;
        }
        $this->couponCode = $couponCode;
        return $this;
    }

    /**
     * The number of items in the transaction
     *
     * @return integer
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * Specify the number of items in the transaction.
     *
     * @param integer $itemsCount
     * @return Transaction
     */
    public function setItemCount($itemsCount)
    {
        if (!$this->isInteger($itemsCount, 'itemCount')) {
            return $this;
        }
        $this->itemCount = $itemsCount;
        return $this;
    }

    /**
     * The shipping method.
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * Specify the shipping method.
     *
     * @param string $shippingMethod
     * @return Transaction
     */
    public function setShippingMethod($shippingMethod)
    {
        if (!$this->isNoEmptyString($shippingMethod, 'shippingMethod')) {
            return $this;
        }
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * Payment method
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Specify the payment method used
     *
     * @param string $paymentMethod
     * @return Transaction
     */
    public function setPaymentMethod($paymentMethod)
    {
        if (!$this->isNoEmptyString($paymentMethod, 'paymentMethod')) {
            return $this;
        }
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * Total revenue associated with the transaction
     *
     * @return float
     */
    public function getTotalRevenue()
    {
        return $this->totalRevenue;
    }

    /**
     * Specify the total revenue associated with the transaction.
     *     NOTE: This value should include any shipping and/or tax amounts.
     *
     * @param float $totalRevenue
     * @return Transaction
     */
    public function setTotalRevenue($totalRevenue)
    {
        if (!$this->isNumeric($totalRevenue, 'totalRevenue')) {
            return $this;
        }
        $this->totalRevenue = $totalRevenue;
        return $this;
    }

    /**
     * Total shipping cost of the transaction
     *
     * @return float
     */
    public function getShippingCosts()
    {
        return $this->shippingCosts;
    }

    /**
     * Specify the total shipping cost of the transaction\
     *
     * @param float $shippingCosts
     * @return Transaction
     */
    public function setShippingCosts($shippingCosts)
    {
        if (!$this->isNumeric($shippingCosts, 'shippingCosts')) {
            return $this;
        }
        $this->shippingCosts = $shippingCosts;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys()
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
    public function isReady()
    {
        return parent::isReady() && $this->getTransactionId() && $this->getAffiliation();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}
