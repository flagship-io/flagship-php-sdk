<?php

declare(strict_types=1);

namespace Flagship\Hit;

use Flagship\Enum\HitType;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\DecisionApiConfig;

class ItemTest extends TestCase
{
    use PHPMock;
    public function testConstruct()
    {
        $round = $this->getFunctionMock("Flagship\Traits", 'round');
        $round->expects($this->any())->willReturn(0);
        $transactionId = "transactionId";
        $itemName = "itemName";
        $itemCode = "itemCode";
        $visitorId = "visitorId";
        $envId = "envId";
        $itemPrice = 125.0;
        $itemQuantity = 45;
        $itemCategory = "category 1";

        $itemArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM      => $visitorId,
            FlagshipConstant::DS_API_ITEM              => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM               => HitType::ITEM->value,
            FlagshipConstant::CUSTOMER_UID             => null,
            FlagshipConstant::QT_API_ITEM              => 0.0,
            FlagshipConstant::TID_API_ITEM             => $transactionId,
            FlagshipConstant::IN_API_ITEM              => $itemName,
            FlagshipConstant::IC_API_ITEM              => $itemCode,
        ];

        $item = new Item($transactionId, $itemName, $itemCode);
        $config = new DecisionApiConfig($envId);

        $item->setVisitorId($visitorId)->setConfig($config)->setDs(FlagshipConstant::SDK_APP);

        $this->assertSame($itemArray, $item->toApiKeys());

        $item->setItemPrice($itemPrice);

        $itemArray[FlagshipConstant::IP_API_ITEM] = $itemPrice;

        $this->assertSame($itemArray, $item->toApiKeys());

        $item->setItemQuantity($itemQuantity);

        $item->setItemCategory($itemCategory);

        $itemArray[FlagshipConstant::IQ_API_ITEM] = $itemQuantity;
        $itemArray[FlagshipConstant::IV_API_ITEM] = $itemCategory;

        $this->assertSame($itemArray, $item->toApiKeys());

        $logManagerMock = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $item->getConfig()->setLogManager($logManagerMock);

        $this->assertSame($itemArray, $item->toApiKeys());
    }



    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $transactionId = "transactionId";
        $itemName = "itemName";
        $itemCode = "itemCode";
        $item = new Item($transactionId, $itemName, $itemCode);
        $item->setVisitorId('visitorId');

        $this->assertFalse($item->isReady());

        //Test with require HitAbstract fields and with null transactionId

        $config = new DecisionApiConfig('envId');


        //Test isReady Test with require HitAbstract fields and  with empty itemName
        $itemName = "";
        $item = new Item($transactionId, $itemName, $itemCode);

        $item->setConfig($config)->setVisitorId('visitorId')->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($item->isReady());
        $this->assertSame(Item::ERROR_MESSAGE, $item->getErrorMessage());

        //Test with require HitAbstract fields and require Item fields
        $itemName = "ItemName";
        $item = new Item($transactionId, $itemName, $itemCode);
        $item->setConfig($config)->setVisitorId('visitorId')->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($item->isReady());
    }
}
