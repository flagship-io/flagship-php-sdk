<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{

    public function testConstruct()
    {
        $transactionId = "transactionId";
        $itemName = "itemName";
        $itemCode = "itemCode";
        $visitorId = "visitorId";
        $envId = "envId";
        $itemPrice = 125;
        $itemQuantity = 45;
        $itemCategory = "category 1";

        $itemArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::ITEM,
            FlagshipConstant::TID_API_ITEM => $transactionId,
            FlagshipConstant::IN_API_ITEM => $itemName,
            FlagshipConstant::IC_API_ITEM => $itemCode
        ];

        $item = new Item($transactionId, $itemName, $itemCode);
        $config = new DecisionApiConfig($envId);

        $item->setVisitorId($visitorId)
            ->setConfig($config)
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertSame($itemArray, $item->toArray());

        $item->setItemPrice($itemPrice);

        $itemArray[FlagshipConstant::IP_API_ITEM] = $itemPrice;

        $this->assertSame($itemArray, $item->toArray());

        $item->setItemQuantity($itemQuantity);

        $item->setItemCategory($itemCategory);

        $itemArray[FlagshipConstant::IQ_API_ITEM] = $itemQuantity;
        $itemArray[FlagshipConstant::IV_API_ITEM] = $itemCategory;

        $this->assertSame($itemArray, $item->toArray());

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

        $errorMessage = function ($itemName, $typeName) {
            $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
            return "[$flagshipSdk] " . sprintf(FlagshipConstant::TYPE_ERROR, $itemName, $typeName);
        };

        $logManagerMock->expects($this->exactly(6))->method('error')->withConsecutive(
            [$errorMessage('transactionId', 'string')],
            [$errorMessage('productName', 'string')],
            [$errorMessage('productSku', 'string')],
            [$errorMessage('itemPrice', 'numeric')],
            [$errorMessage('itemQuantity', 'integer')],
            [$errorMessage('itemCategory', 'string')]
        );

        //Test transition id validation
        $item->setTransactionId('');

        //Test itemName validation
        $item->setProductName(4886);

        //Test itemCode validation
        $item->setProductSku([]);

        //Test itemPrice validation
        $item->setItemPrice("abc");

        //Test itemQuantity validation
        $item->setItemQuantity(12.5);

        //itemCategory
        $item->setItemCategory(7895);

        $this->assertSame($itemArray, $item->toArray());
    }



    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $transactionId = "transactionId";
        $itemName = "itemName";
        $itemCode = "itemCode";
        $item = new Item($transactionId, $itemName, $itemCode);

        $this->assertFalse($item->isReady());

        //Test with require HitAbstract fields and with null transactionId
        $transactionId = null;
        $itemName = "itemName";
        $itemCode = "itemCode";
        $item = new Item($transactionId, $itemName, $itemCode);
        $config = new DecisionApiConfig('envId');
        $item->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($item->isReady());

        //Test isReady Test with require HitAbstract fields and  with empty itemName
        $itemName = "";
        $transactionId = "transactionId";
        $item = new Item($transactionId, $itemName, $itemCode);

        $item->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($item->isReady());
        $this->assertSame(Item::ERROR_MESSAGE, $item->getErrorMessage());

        //Test with require HitAbstract fields and require Item fields
        $transactionId = "transactionId";
        $itemName = "ItemName";
        $item = new Item($transactionId, $itemName, $itemCode);
        $item->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($item->isReady());
    }
}
