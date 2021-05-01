<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{

    public function testConstruct()
    {
        $logManagerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $transactionId = 'transactionId';
        $transactionAffiliation = 'transactionAffi';
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $transaction->setLogManager($logManagerMock);

        $logManagerMock->expects($this->exactly(10))->method('error')
            ->withConsecutive(
                [sprintf(FlagshipConstant::TYPE_ERROR,'transactionId', 'string')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'transactionAffiliation', 'string')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'couponCode', 'string')],
                [sprintf(Transaction::CURRENCY_ERROR, 'currency')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'itemsCount', 'integer')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'paymentMethod', 'string')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'revenue', 'numeric')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'shippingCost', 'numeric')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'shippingMethod', 'string')],
                [sprintf(FlagshipConstant::TYPE_ERROR, 'taxesAmount', 'numeric')]
            );

        $this->assertSame($transactionId, $transaction->getTransactionId());
        $this->assertSame($transactionAffiliation, $transaction->getTransactionAffiliation());

        $transaction->setTransactionId([]);
        $this->assertSame($transactionId, $transaction->getTransactionId());

        $transaction->setTransactionAffiliation([]);
        $this->assertSame($transactionAffiliation, $transaction->getTransactionAffiliation());

        // Test Coupon
        $couponCode = "coupon";
        $transaction->setCouponCode($couponCode);
        $this->assertSame($couponCode, $transaction->getCouponCode());

        //Test Coupon validation
        $transaction->setCouponCode(45);
        $this->assertSame($couponCode, $transaction->getCouponCode());

        //Test currency

        $currency = "USD";
        $transaction->setCurrency($currency);
        $this->assertSame($currency, $transaction->getCurrency());

        //Test currency validation
        $transaction->setCurrency("USDD");
        $this->assertSame($currency, $transaction->getCurrency());

        //Test item count
        $itemCount = 5;
        $transaction->setItemsCount($itemCount);
        $this->assertSame($itemCount, $transaction->getItemsCount());

        //Test item count validation
        $transaction->setItemsCount('abc');
        $this->assertSame($itemCount, $transaction->getItemsCount());

        //Test payment method
        $paymentMethod = 'paypal';
        $transaction->setPaymentMethod($paymentMethod);
        $this->assertSame($paymentMethod, $transaction->getPaymentMethod());

        //Test payment method Validation
        $transaction->setPaymentMethod(78);
        $this->assertSame($paymentMethod, $transaction->getPaymentMethod());

        //Test Revenue
        $revenue = 45;
        $transaction->setRevenue($revenue);
        $this->assertSame($revenue, $transaction->getRevenue());

        //Test Revenue validation
        $transaction->setRevenue('abc');
        $this->assertSame($revenue, $transaction->getRevenue());

        //Test Shipping cost
        $shippingCost = 78;
        $transaction->setShippingCost($shippingCost);
        $this->assertSame($shippingCost, $transaction->getShippingCost());

        //Test Shipping cost validation
        $transaction->setShippingCost('abc');
        $this->assertSame($shippingCost, $transaction->getShippingCost());

        //Test shipping method
        $shippingMethod = "shippingMethod";
        $transaction->setShippingMethod($shippingMethod);
        $this->assertSame($shippingMethod, $transaction->getShippingMethod());

        //Test shipping method validation
        $transaction->setShippingMethod([]);
        $this->assertSame($shippingMethod, $transaction->getShippingMethod());

        //Test Taxes amount
        $taxesAmount = 76;
        $transaction->setTaxesAmount($taxesAmount);
        $this->assertSame($taxesAmount, $transaction->getTaxesAmount());

        //Test Taxes amount validation
        $transaction->setTaxesAmount('abc');
        $this->assertSame($taxesAmount, $transaction->getTaxesAmount());

        $envId = "envId";
        $ds = FlagshipConstant::SDK_APP;
        $visitorId = "visitorId";

        $transaction->setVisitorId($visitorId)->setDs($ds)->setEnvId($envId);

        $transactionArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM=>$visitorId,
            FlagshipConstant::DS_API_ITEM =>$ds,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM =>$envId,
            FlagshipConstant::T_API_ITEM=>HitType::TRANSACTION,
            FlagshipConstant::TID_API_ITEM=>$transactionId,
            FlagshipConstant::TA_API_ITEM=>$transactionAffiliation,
            FlagshipConstant::TT_API_ITEM=>$taxesAmount,
            FlagshipConstant::TC_API_ITEM=>$currency,
            FlagshipConstant::TCC_API_ITEM=>$couponCode,
            FlagshipConstant::ICN_API_ITEM=>$itemCount,
            FlagshipConstant::SM_API_ITEM=>$shippingMethod,
            FlagshipConstant::PM_API_ITEM=>$paymentMethod,
            FlagshipConstant::TR_API_ITEM=>$revenue,
            FlagshipConstant::TS_API_ITEM=>$shippingCost
        ];

        $this->assertSame($transactionArray, $transaction->toArray());
    }
}
