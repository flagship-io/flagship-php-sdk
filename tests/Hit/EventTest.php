<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testConstruct()
    {

        $visitorId = "visitorId";
        $envId = "envId";

        $eventAction = "eventAction";
        $eventCategory = "eventCategory";
        $eventLabel = "eventLabel";
        $eventValue = 458;

        $eventArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::EVENT,
            FlagshipConstant::EVENT_CATEGORY_API_ITEM => $eventCategory,
            FlagshipConstant::EVENT_ACTION_API_ITEM => $eventAction,

        //            FlagshipConstant::EVENT_LABEL_API_ITEM=>$eventLabel,
        //            FlagshipConstant::EVENT_VALUE_API_ITEM =>$eventValue
        ];

        $event = new Event($eventCategory, $eventAction);

        $event->setEnvId($envId)
            ->setVisitorId($visitorId)
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertSame($eventArray, $event->toArray());

        $event->setLabel($eventLabel);
        $eventArray[FlagshipConstant::EVENT_LABEL_API_ITEM] = $eventLabel;

        $this->assertSame($eventArray, $event->toArray());

        $event->setValue($eventValue);
        $eventArray[FlagshipConstant::EVENT_VALUE_API_ITEM] = $eventValue;

        $this->assertSame($eventArray, $event->toArray());

        $logManagerMock = $this->getMockForAbstractClass(
            'Flagship\Utils\LogManagerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $event->setLogManager($logManagerMock);

        $errorMessage = function ($itemName, $typeName) {
            return sprintf(FlagshipConstant::TYPE_ERROR, $itemName, $typeName);
        };

        $logManagerMock->expects($this->exactly(4))->method('error')
            ->withConsecutive(
                [$errorMessage('category', 'string')],
                [$errorMessage('action', 'string')],
                [$errorMessage('label', 'string')],
                [$errorMessage('value', 'numeric')]
            );

        //Test category validation with empty
        $event->setCategory('');

        //Test category validation with no string
        $event->setAction(455);

        //Test label validation with no string
        $event->setLabel([]);

        //Test value validation with no numeric
        $event->setValue('abc');

        $this->assertSame($eventArray, $event->toArray());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $eventCategory = "eventCategory";
        $eventAction = "eventAction";
        $event = new Event($eventCategory, $eventAction);

        $this->assertFalse($event->isReady());

        //Test with require HitAbstract fields and with null eventCategory
        $eventCategory = null;
        $eventAction = "eventAction";
        $event = new Event($eventCategory, $eventAction);

        $event->setEnvId('envId')
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($event->isReady());

        //Test isReady with require HitAbstract fields and  with empty eventAction
        $eventAction = "";
        $eventCategory = "eventCategory";
        $event = new Event($eventCategory, $eventAction);

        $event->setEnvId('envId')
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($event->isReady());

        $this->assertSame(Event::ERROR_MESSAGE, $event->getErrorMessage());

        //Test with require HitAbstract fields and require Transaction fields
        $eventCategory = "eventCategory";
        $eventAction = "ItemName";
        $event = new Event($eventCategory, $eventAction);
        $event->setEnvId('envId')
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($event->isReady());
    }
}
