<?php

declare(strict_types=1);

namespace Flagship\Hit;

use Flagship\Enum\HitType;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\DecisionApiConfig;

class PageTest extends TestCase
{
    use PHPMock;
    public function testConstruct()
    {
        $round = $this->getFunctionMock("Flagship\Traits", 'round');
        $round->expects($this->any())->willReturn(0);
        $pageUrl = 'ScreenName';
        $visitorId = "visitorId";
        $envId = "envId";

        $page = new Page($pageUrl);
        $config = new DecisionApiConfig($envId);

        $page->setConfig($config)
            ->setDs(FlagshipConstant::SDK_APP)->setVisitorId($visitorId);

        $screenArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM      => $visitorId,
            FlagshipConstant::DS_API_ITEM              => FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $envId,
            FlagshipConstant::T_API_ITEM               => HitType::PAGE_VIEW->value,
            FlagshipConstant::CUSTOMER_UID             => null,
            FlagshipConstant::QT_API_ITEM              => 0.0,
            FlagshipConstant::DL_API_ITEM              => $pageUrl,
        ];

        $this->assertSame($screenArray, $page->toApiKeys());
    }

    public function testIsReady()
    {
        //Test isReady without require HitAbstract fields
        $pageUrl = "pageUrl";
        $page = new Page($pageUrl);
        $page->setVisitorId('visitorId');

        $this->assertFalse($page->isReady());


        $config = new DecisionApiConfig('envId');

        $page->setConfig($config)->setVisitorId('visitorId')->setDs(FlagshipConstant::SDK_APP);

        //Test isReady Test with require HitAbstract fields and  with empty pageUrl
        $pageUrl = "";
        $page = new Page($pageUrl);

        $page->setConfig($config)->setVisitorId('visitorId')->setDs(FlagshipConstant::SDK_APP);

        $this->assertFalse($page->isReady());

        $this->assertSame(Page::ERROR_MESSAGE, $page->getErrorMessage());

        //Test with require HitAbstract fields and require Page fields
        $pageUrl = "https://localhost";
        $page = new Page($pageUrl);
        $page->setConfig($config)->setVisitorId('visitorId')->setDs(FlagshipConstant::SDK_APP);

        $this->assertTrue($page->isReady());
    }
}
