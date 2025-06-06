<?php

declare(strict_types=1);

namespace Flagship\Hit;

use Flagship\Config\DecisionApiConfig;
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
        $config = new DecisionApiConfig($envId);

        $screen = new Screen($screenName);
        $screen->setConfig($config)->setDs(FlagshipConstant::SDK_APP)->setVisitorId($visitorId);

        $screenArray = [
                        FlagshipConstant::VISITOR_ID_API_ITEM      => $visitorId,
                        FlagshipConstant::DS_API_ITEM              => FlagshipConstant::SDK_APP,
                        FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
                        FlagshipConstant::T_API_ITEM               => HitType::SCREEN_VIEW->value,
                        FlagshipConstant::CUSTOMER_UID             => null,
                        FlagshipConstant::QT_API_ITEM              => 0.0,
                        FlagshipConstant::DL_API_ITEM              => $screenName,
                       ];

        $this->assertSame($screenArray, $screen->toApiKeys());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $screenName = "screenName";
        $screen = new Screen($screenName);
        $screen->setVisitorId('visitorId');

        $this->assertFalse($screen->isReady());

        //Test with require HitAbstract fields and with null screenName
        $screenName = "";
        $screen = new Screen($screenName);
        $config = new DecisionApiConfig('envId');
        $screen->setConfig($config)->setVisitorId('visitorId')->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($screen->isReady());

        $this->assertSame(Screen::ERROR_MESSAGE, $screen->getErrorMessage());

        //Test with require HitAbstract fields and require Page fields
        $screenName = "screenName";
        $screen = new Screen($screenName);

        $screen->setConfig($config)->setVisitorId('visitorId')->setDs(FlagshipConstant::SDK_APP);
        $this->assertTrue($screen->isReady());
    }
}
