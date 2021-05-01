<?php


namespace Flagship\Hit;


use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Transaction extends HitAbstract
{
    const CURRENCY_ERROR= "'%s' must be a string and have exactly 3 letters";
    private $transactionId;
    private $transactionAffiliation;
    private $taxesAmount;
    private $currency;
    private $couponCode;
    private $itemsCount;
    private $shippingMethod;
    private $paymentMethod;
    private $revenue;
    private $shippingCost;

    /**
     * Transaction constructor.
     * @param string $transactionId
     * @param string $transactionAffiliation
     */
    public function __construct($transactionId, $transactionAffiliation)
    {
        parent::__construct(HitType::TRANSACTION);
        $this->setTransactionId($transactionId);
        $this->setTransactionAffiliation($transactionAffiliation);
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set transaction id
     *
     * @param string $transactionId
     * @return Transaction
     */
    public function setTransactionId($transactionId)
    {
        if (!is_string($transactionId)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'transactionId', 'string'));
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
    public function getTransactionAffiliation()
    {
        return $this->transactionAffiliation;
    }

    /**
     * set transaction affiliation
     *
     * @param string $transactionAffiliation
     * @return Transaction
     */
    public function setTransactionAffiliation($transactionAffiliation)
    {
        if (!is_string($transactionAffiliation)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'transactionAffiliation', 'string'));
            return $this;
        }
        $this->transactionAffiliation = $transactionAffiliation;
        return $this;
    }

    /**
     * Total amount of taxes
     * @return float
     */
    public function getTaxesAmount()
    {
        return $this->taxesAmount;
    }

    /**
     * Specifies the total amount of taxes
     *
     * @param float $taxesAmount
     * @return Transaction
     */
    public function setTaxesAmount($taxesAmount)
    {
        if (!is_numeric($taxesAmount)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'taxesAmount', 'numeric'));
            return $this;
        }
        $this->taxesAmount = $taxesAmount;
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
     * Specifies the currency of transaction.
     *     NOTE: This value should be a valid ISO 4217 currency code.
     *
     * @param string $currency
     * @return Transaction
     */
    public function setCurrency($currency)
    {
        if (!is_string($currency) || strlen($currency)<0 || strlen($currency)>3) {
            $this->logError($this->logManager,
                sprintf(self::CURRENCY_ERROR, 'currency'));
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
     * Specifies the coupon code used by the customer
     * @param string $couponCode
     * @return Transaction
     */
    public function setCouponCode($couponCode)
    {
        if (!is_string($couponCode)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'couponCode', 'string'));
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
    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * Specifies the number of items in the transaction.
     * @param integer $itemsCount
     * @return Transaction
     */
    public function setItemsCount($itemsCount)
    {
        if (!is_int($itemsCount)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'itemsCount', 'integer'));
            return $this;
        }
        $this->itemsCount = $itemsCount;
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
     * Specifies the shipping method.
     *
     * @param string $shippingMethod
     * @return Transaction
     */
    public function setShippingMethod($shippingMethod)
    {
        if (!is_string($shippingMethod)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'shippingMethod', 'string'));
            return $this;
        }
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * Payment method
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Specifies the payment method used
     *
     * @param string $paymentMethod
     * @return Transaction
     */
    public function setPaymentMethod($paymentMethod)
    {
        if (!is_string($paymentMethod)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'paymentMethod', 'string'));
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
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     *Specifies the total revenue associated with the transaction.
     *     This value should include any shipping and/or tax amounts.
     * @param float $revenue
     * @return Transaction
     */
    public function setRevenue($revenue)
    {
        if (!is_numeric($revenue)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'revenue', 'numeric'));
            return $this;
        }
        $this->revenue = $revenue;
        return $this;
    }

    /**
     * Total shipping cost of the transaction
     * @return string
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * Specifies the total shipping cost of the transaction
     * @param float $shippingCost
     * @return Transaction
     */
    public function setShippingCost($shippingCost)
    {
        if (!is_numeric($shippingCost)) {
            $this->logError($this->logManager,
                sprintf(FlagshipConstant::TYPE_ERROR, 'shippingCost', 'numeric'));
            return $this;
        }
        $this->shippingCost = $shippingCost;
        return $this;
    }

    public function toArray()
    {
        $arrayParent = parent::toArray();
        $arrayParent[FlagshipConstant::TID_API_ITEM]= $this->getTransactionId();
        $arrayParent[FlagshipConstant::TA_API_ITEM]= $this->getTransactionAffiliation();
        $arrayParent[FlagshipConstant::TT_API_ITEM]= $this->getTaxesAmount();
        $arrayParent[FlagshipConstant::TC_API_ITEM]= $this->getCurrency();
        $arrayParent[FlagshipConstant::TCC_API_ITEM]= $this->getCouponCode();
        $arrayParent[FlagshipConstant::ICN_API_ITEM]= $this->getItemsCount();
        $arrayParent[FlagshipConstant::SM_API_ITEM]= $this->getShippingMethod();
        $arrayParent[FlagshipConstant::PM_API_ITEM]= $this->getPaymentMethod();
        $arrayParent[FlagshipConstant::TR_API_ITEM]= $this->getRevenue();
        $arrayParent[FlagshipConstant::TS_API_ITEM]= $this->getShippingCost();

        return $arrayParent;
    }

}