<?php

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
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
        $eventCategory = EventCategory::USER_ENGAGEMENT;
        $eventLabel = "eventLabel";
        $eventValue = 458;
        $userIp = "127.0.0.1";
        $screenResolution = "200X200";
        $userLanguage = "Fr";
        $sessionNumber = 1;

        $eventArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::EVENT,
            FlagshipConstant::USER_IP_API_ITEM => $userIp,
            FlagshipConstant::SCREEN_RESOLUTION_API_ITEM => $screenResolution,
            FlagshipConstant::USER_LANGUAGE => $userLanguage,
            FlagshipConstant::SESSION_NUMBER => $sessionNumber,
            FlagshipConstant::CUSTOMER_UID => null,
            FlagshipConstant::EVENT_CATEGORY_API_ITEM => $eventCategory,
            FlagshipConstant::EVENT_ACTION_API_ITEM => $eventAction,
        ];

        $event = new Event($eventCategory, $eventAction);
        $config = new DecisionApiConfig();
        $config->setEnvId($envId);
        $event->setConfig($config)
            ->setVisitorId($visitorId)
            ->setDs(FlagshipConstant::SDK_APP)
            ->setLocale($userLanguage)
            ->setUserIP($userIp)
            ->setScreenResolution($screenResolution)
            ->setSessionNumber($sessionNumber);

        $this->assertSame($eventArray, $event->toApiKeys());

        $event->setLabel($eventLabel);
        $eventArray[FlagshipConstant::EVENT_LABEL_API_ITEM] = $eventLabel;

        $this->assertSame($eventArray, $event->toApiKeys());

        $event->setValue($eventValue);
        $eventArray[FlagshipConstant::EVENT_VALUE_API_ITEM] = $eventValue;

        $this->assertSame($eventArray, $event->toApiKeys());

        $logManagerMock = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $event->getConfig()->setLogManager($logManagerMock);

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $errorMessage = function ($itemName, $typeName) use ($flagshipSdk) {

            return "[$flagshipSdk] " . sprintf(FlagshipConstant::TYPE_ERROR, $itemName, $typeName);
        };

        $logManagerMock->expects($this->exactly(5))->method('error')
            ->withConsecutive(
                ["[$flagshipSdk] " . sprintf(Event::CATEGORY_ERROR, 'category')],
                [$errorMessage('action', 'string')],
                [$errorMessage('eventLabel', 'string')],
                ["[$flagshipSdk] " . Event::VALUE_FIELD_ERROR,['TAG' => 'setValue']]
            );

        //Test category validation with empty
        $event->setCategory('');

        //Test category validation with no string
        $event->setAction(455);

        //Test label validation with no string
        $event->setLabel([]);

        //Test value validation with no numeric
        $event->setValue('abc');

        //Test value validation with no numeric
        $event->setValue(2.5);

        $this->assertSame($eventArray, $event->toApiKeys());

        $anonymousId = "anonymousId";
        $event->setAnonymousId($anonymousId);

        $eventArray[FlagshipConstant::VISITOR_ID_API_ITEM] = $anonymousId;
        $eventArray[FlagshipConstant::CUSTOMER_UID] = $visitorId;
        $this->assertSame($eventArray, $event->toApiKeys());

        $this->assertEquals($anonymousId, $event->getAnonymousId());
    }

    public function testSetCategory()
    {
        $eventAction = 'action';
        $event = new Event(EventCategory::ACTION_TRACKING, $eventAction);
        $event->setConfig(new DecisionApiConfig());
        $this->assertSame(EventCategory::ACTION_TRACKING, $event->getCategory());

        $event->setCategory(EventCategory::USER_ENGAGEMENT);

        $this->assertSame(EventCategory::USER_ENGAGEMENT, $event->getCategory());

        $event->setCategory("otherCat");

        $this->assertSame(EventCategory::USER_ENGAGEMENT, $event->getCategory());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $eventCategory = EventCategory::USER_ENGAGEMENT;
        $eventAction = "eventAction";
        $event = new Event($eventCategory, $eventAction);

        $this->assertFalse($event->isReady());

        //Test with require HitAbstract fields and with null eventCategory
        $eventCategory = null;
        $eventAction = "eventAction";
        $event = new Event($eventCategory, $eventAction);
        $config = new DecisionApiConfig("envId");
        $event->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($event->isReady());

        //Test isReady with require HitAbstract fields and  with empty eventAction
        $eventAction = "";
        $eventCategory = EventCategory::ACTION_TRACKING;
        $event = new Event($eventCategory, $eventAction);

        $event->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($event->isReady());

        $this->assertSame(Event::ERROR_MESSAGE, $event->getErrorMessage());

        //Test with require HitAbstract fields and require Transaction fields
        $eventCategory = EventCategory::ACTION_TRACKING;
        $eventAction = "ItemName";
        $event = new Event($eventCategory, $eventAction);
        $event->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($event->isReady());
    }
}
