<?php

namespace Flagship\Hit;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use PHPUnit\Framework\TestCase;

class ScreenTest extends TestCase
{

    public function testConstruct()
    {
        $screenName = 'screenName';
        $visitorId = "visitorId";
        $envId = "envId";
        $config = new FlagshipConfig($envId);

        $screen = new Screen($screenName);
        $screen->setConfig($config)
            ->setDs(FlagshipConstant::SDK_APP)
            ->setVisitorId($visitorId);

        $screenArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::SCREEN_VIEW,
            FlagshipConstant::DL_API_ITEM => $screenName
        ];

        $this->assertSame($screenArray, $screen->toArray());

        $screen->setScreenName([]);
        $this->assertSame($screenName, $screen->getScreenName());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $screenName = "screenName";
        $screen = new Screen($screenName);

        $this->assertFalse($screen->isReady());

        //Test with require HitAbstract fields and with null screenName
        $screenName = null;
        $screen = new Screen($screenName);
        $config = new FlagshipConfig('envId');
        $screen->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($screen->isReady());

        //Test isReady Test with require HitAbstract fields and  with empty screenName
        $screenName = "";
        $screen = new Screen($screenName);

        $screen->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($screen->isReady());

        $this->assertSame(Screen::ERROR_MESSAGE, $screen->getErrorMessage());

        //Test with require HitAbstract fields and require Page fields
        $screenName = "screenName";
        $screen = new Screen($screenName);

        $screen->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);
        $this->assertTrue($screen->isReady());
    }
}
