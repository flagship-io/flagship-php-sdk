<?php

namespace Flagship\Hit;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{

    public function testConstruct()
    {
        $pageUrl = 'ScreenName';
        $visitorId = "visitorId";
        $envId = "envId";

        $page = new Page($pageUrl);
        $config = new FlagshipConfig($envId);

        $page->setConfig($config)
            ->setDs(FlagshipConstant::SDK_APP)
            ->setVisitorId($visitorId);

        $screenArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM => HitType::PAGE_VIEW,
            FlagshipConstant::DL_API_ITEM => $pageUrl
        ];

        $this->assertSame($screenArray, $page->toArray());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $pageUrl = "pageUrl";
        $page = new Page($pageUrl);

        $this->assertFalse($page->isReady());

        //Test with require HitAbstract fields and with null pageUrl
        $pageUrl = null;
        $page = new Page($pageUrl);
        $config = new FlagshipConfig('envId');

        $page->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($page->isReady());

        //Test isReady Test with require HitAbstract fields and  with empty pageUrl
        $pageUrl = "";
        $page = new Page($pageUrl);

        $page->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($page->isReady());

        $this->assertSame(Page::ERROR_MESSAGE, $page->getErrorMessage());

        //Test with require HitAbstract fields and require Page fields
        $pageUrl = "https://localhost";
        $page = new Page($pageUrl);
        $page->setConfig($config)
            ->setVisitorId('visitorId')
            ->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($page->isReady());
    }
}
