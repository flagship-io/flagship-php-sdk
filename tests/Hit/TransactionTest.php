<?php

declare(strict_types=1);

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
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
        $config = new DecisionApiConfig($envId);

        $transaction->setVisitorId($visitorId)->setDs($ds)->setConfig($config);

        $transactionArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => $ds,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::TRANSACTION->value,
            FlagshipConstant::CUSTOMER_UID => null,
            FlagshipConstant::QT_API_ITEM => 0.0,
            FlagshipConstant::TID_API_ITEM => $transactionId,
            FlagshipConstant::TA_API_ITEM => $transactionAffiliation
        ];

        $taxesAmount = 76.0;
        $transaction->setTaxes($taxesAmount);
        $transactionArray[FlagshipConstant::TT_API_ITEM] = $taxesAmount;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $currency = "USD";
        $transaction->setCurrency($currency);
        $transactionArray[FlagshipConstant::TC_API_ITEM] = $currency;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $couponCode = "coupon";
        $transaction->setCouponCode($couponCode);
        $transactionArray[FlagshipConstant::TCC_API_ITEM] = $couponCode;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $itemCount = 5;
        $transaction->setItemCount($itemCount);
        $transactionArray[FlagshipConstant::ICN_API_ITEM] = $itemCount;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $shippingMethod = "shippingMethod";
        $transaction->setShippingMethod($shippingMethod);
        $transactionArray[FlagshipConstant::SM_API_ITEM] = $shippingMethod;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $paymentMethod = 'paypal';
        $transaction->setPaymentMethod($paymentMethod);
        $transactionArray[FlagshipConstant::PM_API_ITEM] = $paymentMethod;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $revenue = 45.0;
        $transaction->setTotalRevenue($revenue);
        $transactionArray[FlagshipConstant::TR_API_ITEM] = $revenue;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $shippingCost = 78.0;
        $transaction->setShippingCosts($shippingCost);
        $transactionArray[FlagshipConstant::TS_API_ITEM] = $shippingCost;


        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $transaction->getConfig()->setLogManager($logManagerMock);
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $transactionId = "transactionId";
        $transactionAffiliation = "transactionAffiliation";
        $transaction = new Transaction($transactionId, $transactionAffiliation);
        $transaction->setVisitorId('visitorId');

        $this->assertFalse($transaction->isReady());

        //Test with require HitAbstract fields and with null transactionId
        $config = new DecisionApiConfig('envId');

        $transaction->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);


        //Test isReady with require HitAbstract fields and  with empty transactionAffiliation
        $transactionAffiliation = "";
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $transaction->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($transaction->isReady());

        $this->assertSame(Transaction::ERROR_MESSAGE, $transaction->getErrorMessage());

        //Test with require HitAbstract fields and require Transaction fields
        $transactionAffiliation = "ItemName";
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $transaction->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($transaction->isReady());
    }
}
