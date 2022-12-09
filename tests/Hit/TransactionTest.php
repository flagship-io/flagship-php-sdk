<?php

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
            FlagshipConstant::T_API_ITEM => HitType::TRANSACTION,
            FlagshipConstant::USER_IP_API_ITEM => null,
            FlagshipConstant::SCREEN_RESOLUTION_API_ITEM => null,
            FlagshipConstant::USER_LANGUAGE => null,
            FlagshipConstant::SESSION_NUMBER => null,
            FlagshipConstant::CUSTOMER_UID => null,
            FlagshipConstant::TID_API_ITEM => $transactionId,
            FlagshipConstant::TA_API_ITEM => $transactionAffiliation
        ];

        $taxesAmount = 76;
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

        $revenue = 45;
        $transaction->setTotalRevenue($revenue);
        $transactionArray[FlagshipConstant::TR_API_ITEM] = $revenue;

        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $shippingCost = 78;
        $transaction->setShippingCosts($shippingCost);
        $transactionArray[FlagshipConstant::TS_API_ITEM] = $shippingCost;


        $this->assertSame($transactionArray, $transaction->toApiKeys());

        $transaction->getConfig()->setLogManager($logManagerMock);

        $errorMessage = function ($itemName, $typeName) {
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return "[$flagshipSdk] " . sprintf(FlagshipConstant::TYPE_ERROR, $itemName, $typeName);
        };
        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManagerMock->expects($this->exactly(10))->method('error')
            ->withConsecutive(
                [$errorMessage('transactionId', 'string')],
                [$errorMessage('affiliation', 'string')],
                [$errorMessage('couponCode', 'string')],
                ["[$flagshipSdk] " . sprintf(Transaction::CURRENCY_ERROR, 'currency')],
                [$errorMessage('itemCount', 'integer')],
                [$errorMessage('paymentMethod', 'string')],
                [$errorMessage('totalRevenue', 'numeric')],
                [$errorMessage('shippingCosts', 'numeric')],
                [$errorMessage('shippingMethod', 'string')],
                [$errorMessage('taxes', 'numeric')]
            );

        $transaction->setTransactionId([]);
        $this->assertSame($transactionId, $transaction->getTransactionId());

        $transaction->setAffiliation([]);
        $this->assertSame($transactionAffiliation, $transaction->getAffiliation());

        //Test Coupon validation
        $transaction->setCouponCode('');
        $this->assertSame($couponCode, $transaction->getCouponCode());

        //Test currency validation
        $transaction->setCurrency("USDD");
        $this->assertSame($currency, $transaction->getCurrency());

        //Test item count validation
        $transaction->setItemCount('abc');
        $this->assertSame($itemCount, $transaction->getItemCount());

        //Test payment method Validation
        $transaction->setPaymentMethod(78);
        $this->assertSame($paymentMethod, $transaction->getPaymentMethod());

        //Test Revenue validation
        $transaction->setTotalRevenue('abc');
        $this->assertSame($revenue, $transaction->getTotalRevenue());

        //Test Shipping cost validation
        $transaction->setShippingCosts('abc');
        $this->assertSame($shippingCost, $transaction->getShippingCosts());

        //Test shipping method validation
        $transaction->setShippingMethod([]);
        $this->assertSame($shippingMethod, $transaction->getShippingMethod());

        //Test Taxes amount validation
        $transaction->setTaxes('abc');
        $this->assertSame($taxesAmount, $transaction->getTaxes());
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
        $config = new DecisionApiConfig('envId');

        $transaction->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($transaction->isReady());

        //Test isReady with require HitAbstract fields and  with empty transactionAffiliation
        $transactionAffiliation = "";
        $transactionId = "transactionId";
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $transaction->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($transaction->isReady());

        $this->assertSame(Transaction::ERROR_MESSAGE, $transaction->getErrorMessage());

        //Test with require HitAbstract fields and require Transaction fields
        $transactionId = "transactionId";
        $transactionAffiliation = "ItemName";
        $transaction = new Transaction($transactionId, $transactionAffiliation);

        $transaction->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($transaction->isReady());
    }
}
