<?php

namespace Flagship\Hit;

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
        $page->setEnvId($envId)
            ->setDs(FlagshipConstant::SDK_APP)
            ->setVisitorId($visitorId);

        $screenArray = [
            FlagshipConstant::VISITOR_ID_API_ITEM=>$visitorId,
            FlagshipConstant::DS_API_ITEM =>FlagshipConstant::SDK_APP,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM =>$envId,
            FlagshipConstant::T_API_ITEM=>HitType::PAGE_VIEW,
            FlagshipConstant::DL_API_ITEM=>$pageUrl
        ];

        $this->assertSame($screenArray, $page->toArray());
    }
}
