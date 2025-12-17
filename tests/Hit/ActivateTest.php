<?php

namespace Flagship\Hit;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Flagship\Flag\FSFlagMetadata;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\DecisionApiConfig;

class ActivateTest extends TestCase
{
    use PHPMock;
    public function testTestConstruct()
    {
        $variationId = "varId";
        $variationName = "variationName";
        $variationGroupId = "varGrId";
        $variationGroupName = "variationGroupName";
        $envId = "envId";
        $visitorId = "visitorId";
        $flagKey = "key";
        $flagValue = "value";
        $campaignName = "campaignName";
        $visitorContext = ["key" => "value"];
        $flagMetadata = new FSFlagMetadata(
            "campaignId",
            $variationGroupId,
            $variationId,
            false,
            "ab",
            null,
            $campaignName,
            $variationGroupName,
            $variationName
        );

        $config = new DecisionApiConfig($envId);

        $round = $this->getFunctionMock("Flagship\Traits", 'round');
        $round->expects($this->any())->willReturn(123456789);

        $activate = new Activate($variationGroupId, $variationId, $flagKey, $flagMetadata);
        $activate->setConfig($config)->setDs(FlagshipConstant::SDK_APP)
        ->setVisitorId($visitorId);

        $this->assertSame($variationId, $activate->getVariationId());
        $this->assertSame($variationGroupId, $activate->getVariationGroupId());

        $variationId = "varId2";
        $variationGroupId = "varGrId2";

        $activate->setVariationId($variationId)->setVariationGroupId($variationGroupId);

        $this->assertSame($variationId, $activate->getVariationId());
        $this->assertSame($variationGroupId, $activate->getVariationGroupId());

        $apiKeys = [
            FlagshipConstant::VISITOR_ID_API_ITEM         => $visitorId,
            FlagshipConstant::VARIATION_ID_API_ITEM       => $variationId,
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $variationGroupId,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM    => $envId,
            FlagshipConstant::ANONYMOUS_ID                => null,
            FlagshipConstant::QT_API_ITEM                 => 0.0,
        ];

        $this->assertSame($apiKeys, $activate->toApiKeys());

        $anonymousId = "anonymousId";
        $activate->setAnonymousId($anonymousId);

        $apiKeys = [
            FlagshipConstant::VISITOR_ID_API_ITEM         => $visitorId,
            FlagshipConstant::VARIATION_ID_API_ITEM       => $variationId,
            FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $variationGroupId,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM    => $envId,
            FlagshipConstant::ANONYMOUS_ID                => $anonymousId,
            FlagshipConstant::QT_API_ITEM                 => 0.0,
        ];

        $this->assertSame($apiKeys, $activate->toApiKeys());

        $this->assertTrue($activate->isReady());

        $this->assertSame(Activate::ERROR_MESSAGE, $activate->getErrorMessage());

        $activate->setFlagKey($flagKey)->setFlagValue($flagValue)->setFlagMetadata($flagMetadata)->setVisitorContext($visitorContext);

        $this->assertSame($flagKey, $activate->getFlagKey());
        $this->assertSame($flagValue, $activate->getFlagValue());
        $this->assertSame($flagMetadata, $activate->getFlagMetadata());
        $this->assertSame($visitorContext, $activate->getVisitorContext());
    }
}
