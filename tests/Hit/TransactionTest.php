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
            'Psr\Log\LoggerInterface',
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

        $envId = "envId";
        $ds = FlagshipConstant::SDK_APP;
        $visitorId = "visitorId";

        $transaction->setVisitorId($visitorId)->setDs($ds)->setEnvId($envId);

        $transactionArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => $ds,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::TRANSACTION,
            FlagshipConstant::TID_API_ITEM => $transactionId,
            FlagshipConstant::TA_API_ITEM => $transactionAffiliation
        ];

        $taxesAmount = 76;
        $transaction->setTaxesAmount($taxesAmount);
        $transactionArray[FlagshipConstant::TT_API_ITEM] = $taxesAmount;

        $this->assertSame($transactionArray, $transaction->toArray());

        $currency = "USD";
        $transaction->setCurrency($currency);
        $transactionArray[FlagshipConstant::TC_API_ITEM] = $currency;

        $this->assertSame($transactionArray, $transaction->toArray());

        $couponCode = "coupon";
        $transaction->setCouponCode($couponCode);
        $transactionArray[FlagshipConstant::TCC_API_ITEM] = $couponCode;

        $this->assertSame($transactionArray, $transaction->toArray());

        $itemCount = 5;
        $transaction->setItemsCount($itemCount);
        $transactionArray[FlagshipConstant::ICN_API_ITEM] = $itemCount;

        $this->assertSame($transactionArray, $transaction->toArray());

        $shippingMethod = "shippingMethod";
        $transaction->setShippingMethod($shippingMethod);
        $transactionArray[FlagshipConstant::SM_API_ITEM] = $shippingMethod;

        $this->assertSame($transactionArray, $transaction->toArray());

        $paymentMethod = 'paypal';
        $transaction->setPaymentMethod($paymentMethod);
        $transactionArray[FlagshipConstant::PM_API_ITEM] = $paymentMethod;

        $this->assertSame($transactionArray, $transaction->toArray());

        $revenue = 45;
        $transaction->setRevenue($revenue);
        $transactionArray[FlagshipConstant::TR_API_ITEM] = $revenue;

        $this->assertSame($transactionArray, $transaction->toArray());

        $shippingCost = 78;
        $transaction->setShippingCost($shippingCost);
        $transactionArray[FlagshipConstant::TS_API_ITEM] = $shippingCost;


        $this->assertSame($transactionArray, $transaction->toArray());

        $transaction->setLogManager($logManagerMock);

        $logManagerMock->expects($this->exactly(10))->method('error')
            ->withConsecutive(
                [sprintf(FlagshipConstant::TYPE_ERROR, 'transactionId', 'string')],
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

        $transaction->setTransactionId([]);
        $this->assertSame($transactionId, $transaction->getTransactionId());

        $transaction->setTransactionAffiliation([]);
        $this->assertSame($transactionAffiliation, $transaction->getTransactionAffiliation());

        //Test Coupon validation
        $transaction->setCouponCode('');
        $this->assertSame($couponCode, $transaction->getCouponCode());

        //Test currency validation
        $transaction->setCurrency("USDD");
        $this->assertSame($currency, $transaction->getCurrency());

        //Test item count validation
        $transaction->setItemsCount('abc');
        $this->assertSame($itemCount, $transaction->getItemsCount());

        //Test payment method Validation
        $transaction->setPaymentMethod(78);
        $this->assertSame($paymentMethod, $transaction->getPaymentMethod());

        //Test Revenue validation
        $transaction->setRevenue('abc');
        $this->assertSame($revenue, $transaction->getRevenue());

        //Test Shipping cost validation
        $transaction->setShippingCost('abc');
        $this->assertSame($shippingCost, $transaction->getShippingCost());

        //Test shipping method validation
        $transaction->setShippingMethod([]);
        $this->assertSame($shippingMethod, $transaction->getShippingMethod());

        //Test Taxes amount validation
        $transaction->setTaxesAmount('abc');
        $this->assertSame($taxesAmount, $transaction->getTaxesAmount());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $transactionId = "transactionId";
        $transactionAffiliation = "transactionAffiliation";
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $this->assertFalse($transaction->isReady());

        //Test with require HitAbstract fields and with null transactionId
        $transactionId = null;
        $transactionAffiliation = "transactionAffiliation";
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $transaction->setEnvId('envId')
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($transaction->isReady());

        //Test isReady with require HitAbstract fields and  with empty transactionAffiliation
        $transactionAffiliation = "";
        $transactionId = "transactionId";
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $transaction->setEnvId('envId')
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($transaction->isReady());

        $this->assertSame(Transaction::ERROR_MESSAGE, $transaction->getErrorMessage());

        //Test with require HitAbstract fields and require Transaction fields
        $transactionId = "transactionId";
        $transactionAffiliation = "ItemName";
        $transaction = new Transaction($transactionId, $transactionAffiliation);
        $transaction->setEnvId('envId')
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($transaction->isReady());
    }
}
