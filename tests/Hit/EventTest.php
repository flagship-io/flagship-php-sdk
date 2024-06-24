<?php

declare(strict_types=1);

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
        $eventValue = 458.0;
        $userIp = "127.0.0.1";
        $screenResolution = "200X200";
        $userLanguage = "Fr";
        $sessionNumber = 1;

        $eventArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::EVENT->value,
            FlagshipConstant::CUSTOMER_UID => null,
            FlagshipConstant::QT_API_ITEM => 0.0,
            FlagshipConstant::USER_IP_API_ITEM => $userIp,
            FlagshipConstant::SCREEN_RESOLUTION_API_ITEM => $screenResolution,
            FlagshipConstant::USER_LANGUAGE => $userLanguage,
            FlagshipConstant::SESSION_NUMBER => $sessionNumber,
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

        $anonymousId = "anonymousId";
        $event->setAnonymousId($anonymousId);

        $eventArray[FlagshipConstant::VISITOR_ID_API_ITEM] = $anonymousId;
        $eventArray[FlagshipConstant::CUSTOMER_UID] = $visitorId;
        $this->assertSame($eventArray, $event->toApiKeys());

        $this->assertEquals($anonymousId, $event->getAnonymousId());

        $event->setAction("newAction");
        $this->assertEquals("newAction", $event->getAction());
    }

    public function testSetCategory()
    {
        $eventAction = 'action';
        $event = new Event(EventCategory::ACTION_TRACKING, $eventAction);
        $event->setConfig(new DecisionApiConfig());
        $this->assertSame(EventCategory::ACTION_TRACKING, $event->getCategory());

        $event->setCategory(EventCategory::USER_ENGAGEMENT);

        $this->assertSame(EventCategory::USER_ENGAGEMENT, $event->getCategory());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $eventCategory = EventCategory::USER_ENGAGEMENT;
        $eventAction = "eventAction";
        $event = new Event($eventCategory, $eventAction);
        $event->setVisitorId('visitorId');

        $this->assertFalse($event->isReady());

        $config = new DecisionApiConfig("envId", "apiKey");

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
        $eventAction = "ItemName";
        $event = new Event($eventCategory, $eventAction);
        $event->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($event->isReady());
    }
}
