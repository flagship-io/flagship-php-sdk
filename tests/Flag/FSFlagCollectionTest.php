<?php

namespace Flagship\Flag;

use Flagship\Config\DecisionApiConfig;
use Flagship\Model\FlagDTO;
use PHPUnit\Framework\TestCase;

class FSFlagCollectionTest extends TestCase
{
    public function testFlagCollection()
    {
        $visitor = $this->getMockBuilder('Flagship\Visitor\VisitorAbstract')
            ->disableOriginalConstructor()
            ->getMock();

        $visitor->method("getConfig")->willReturn(new DecisionApiConfig());



        $value1 = "value1";
        $value2 = "value2";
        $key1 = "key1";
        $key2 = "key2";
        $flagDTO = new FlagDTO();
        $flagDTO->setIsReference(true)
            ->setVariationId("variationId")
            ->setVariationName("variationName")
            ->setVariationGroupId('varGroupId')
            ->setVariationGroupName("variationGroupName")
            ->setCampaignId("campaignId")
            ->setCampaignName("campaignName")
            ->setKey($key1)->setValue($value1)
            ->setSlug("slug")
            ->setCampaignType("ab");

        $flagMetadata1 = new FSFlagMetadata(
            $flagDTO->getCampaignId(),
            $flagDTO->getVariationGroupId(),
            $flagDTO->getVariationId(),
            $flagDTO->getIsReference(),
            $flagDTO->getCampaignType(),
            $flagDTO->getSlug(),
            $flagDTO->getCampaignName(),
            $flagDTO->getVariationGroupName(),
            $flagDTO->getVariationName()
        );

        $flagDTO2 = new FlagDTO();
        $flagDTO2->setIsReference(true)
            ->setVariationId("variationId")
            ->setVariationName("variationName")
            ->setVariationGroupId('varGroupId')
            ->setVariationGroupName("variationGroupName")
            ->setCampaignId("campaignId")
            ->setCampaignName("campaignName")
            ->setKey($key2)->setValue($value2)
            ->setSlug("slug")
            ->setCampaignType("ab");

        $flagMetadata2 = new FSFlagMetadata(
            $flagDTO2->getCampaignId(),
            $flagDTO2->getVariationGroupId(),
            $flagDTO2->getVariationId(),
            $flagDTO2->getIsReference(),
            $flagDTO2->getCampaignType(),
            $flagDTO2->getSlug(),
            $flagDTO2->getCampaignName(),
            $flagDTO2->getVariationGroupName(),
            $flagDTO2->getVariationName()
        );

        $visitor->method("getFlagsDTO")->willReturn([$flagDTO, $flagDTO2]);

        $flagCollection = new FSFlagCollection($visitor);
        $this->assertEquals(2, $flagCollection->getSize());

        // Test get
        $flag1 = $flagCollection->get($key1);
        $this->assertInstanceOf(FSFlag::class, $flag1);
        $this->assertTrue($flag1->exists());

        $flag2 = $flagCollection->get("key2");
        $this->assertInstanceOf(FSFlag::class, $flag2);
        $this->assertTrue($flag2->exists());

        $flag3 = $flagCollection->get("key3");
        $this->assertInstanceOf(FSFlag::class, $flag3);
        $this->assertFalse($flag3->exists());

        // Test has flag
        $this->assertTrue($flagCollection->has($key1));

        // Test keys
        $this->assertEquals([$key1, $key2], $flagCollection->keys());

        // test filter
        $filtered = $flagCollection->filter(function ($flag, $key, $collection) use ($key1) {
            return  $key === $key1;
        });
        $this->assertEquals(1, $filtered->getSize());
        $this->assertEquals($key1, $filtered->keys()[0]);

        // Test exposeAll
        $visitor->expects($this->exactly(2))->method("visitorExposed")->with(
            $this->logicalOr(
                $this->equalTo($key1),
                $this->equalTo($key2)
            ),
            null,
            $this->logicalOr(
                $this->equalTo($flagDTO),
                $this->equalTo($flagDTO2)
            )
        );
        $flagCollection->exposeAll();

        // Test getMetadata
        $visitor->expects($this->exactly(4))->method("getFlagMetadata")->with(
            $this->logicalOr(
                $this->equalTo($key1),
                $this->equalTo($key2),
            ),
            $this->logicalOr(
                $this->equalTo($flagDTO),
                $this->equalTo($flagDTO2)
            )
        )->willReturnOnConsecutiveCalls($flagMetadata1, $flagMetadata2, $flagMetadata1, $flagMetadata2);
        $metadata = $flagCollection->getMetadata();

        $this->assertEquals(2, count($metadata));
        $this->assertEquals($flagMetadata1, $metadata[$key1]);
        $this->assertEquals($flagMetadata2, $metadata[$key2]);

        // Test toJSON
        $visitor->expects($this->exactly(2))->method("getFlagValue")->with(
            $this->logicalOr(
                $this->equalTo($key1),
                $this->equalTo($key2),
            ),
            $this->equalTo(null),
            $this->logicalOr(
                $this->equalTo($flagDTO),
                $this->equalTo($flagDTO2)
            ),
            $this->logicalOr(
                $this->equalTo(true),
                $this->equalTo(false)
            )
        )->willReturnOnConsecutiveCalls($value1, $value2);
        $json = $flagCollection->toJSON();
        $expectedJson = '[{"key":"key1","campaignId":"campaignId","campaignName":"campaignName","variationGroupId":"varGroupId","variationGroupName":"variationGroupName","variationId":"variationId","variationName":"variationName","isReference":true,"campaignType":"ab","slug":"slug","hex":"7b2276223a2276616c756531227d"},{"key":"key2","campaignId":"campaignId","campaignName":"campaignName","variationGroupId":"varGroupId","variationGroupName":"variationGroupName","variationId":"variationId","variationName":"variationName","isReference":true,"campaignType":"ab","slug":"slug","hex":"7b2276223a2276616c756532227d"}]';
        $this->assertJson($json);
        $this->assertEquals($expectedJson, $json);

        // Test each
        $flagCollection->each(function ($flag, $key, $collection) use ($key1, $key2) {
            $this->assertInstanceOf(FSFlag::class, $flag);
            $this->assertTrue(in_array($key, [$key1, $key2]));
        });

        // Test iterator
        foreach ($flagCollection as $key => $flag) {
            $this->assertInstanceOf(FSFlag::class, $flag);
            $this->assertTrue(in_array($key, [$key1, $key2]));
        }
    }
}
