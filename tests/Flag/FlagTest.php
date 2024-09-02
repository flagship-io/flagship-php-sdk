<?php

namespace Flagship\Flag;

use Flagship\Enum\FSFetchReason;
use Flagship\Enum\FSFetchStatus;
use Flagship\Enum\FSFlagStatus;
use Flagship\Model\FetchFlagsStatus;
use Flagship\Model\FlagDTO;
use PHPUnit\Framework\TestCase;

class FlagTest extends TestCase
{
    public function testFlag()
    {
        $key = "key";
        $defaultValue = "DefaultValue";
        $flagDTO = new FlagDTO();
        $flagDTO->setIsReference(true)->setVariationId("variationId")->setVariationName("variationName")->setVariationGroupId('varGroupId')->setVariationGroupName("variationGroupName")->setCampaignId("campaignId")->setCampaignName("campaignName")->setKey($key)->setValue("value")->setSlug("slug")->setCampaignType("ab");

        $metadata = new FSFlagMetadata(
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
        $visitorDelegateMock = $this->getMockForAbstractClass(
            'Flagship\Visitor\VisitorAbstract',
            [],
            "",
            false,
            false,
            true,
            [
             'getFlagValue',
             'userExposed',
             'getFlagMetadata',
             'getFlagsDTO',
            ],
            '',
        );

        $visitorDelegateMock->method("getFlagsDTO")->willReturn([$flagDTO]);

        $visitorDelegateMock->expects($this->exactly(2))->method('getFlagValue')->with(
            $this->logicalOr(
                $key,
                $key
            ),
            $defaultValue,
            $flagDTO,
            $this->logicalOr(
                true,
                false
            )
        )->willReturn($flagDTO->getValue());

        $flag = new FSFlag($key, $visitorDelegateMock);

        $value = $flag->getValue($defaultValue);
        $this->assertEquals($value, $flagDTO->getValue());

        $value = $flag->getValue($defaultValue, false);
        $this->assertEquals($value, $flagDTO->getValue());

        $this->assertTrue($flag->exists());

        $visitorDelegateMock->expects($this->once())->method('visitorExposed')->with(
            $key,
            $defaultValue,
            $flagDTO
        );

        $flag->visitorExposed();

        $visitorDelegateMock->expects($this->once())->method('getFlagMetadata')->with(
            $key,
            $flagDTO
        )->willReturn($metadata);

        $metadataValue = $flag->getMetadata();

        $this->assertSame($metadataValue, $metadata);

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::PANIC, FSFetchReason::NONE));
        // Test flag status
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::PANIC, $value);

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(
            FSFetchStatus::FETCH_REQUIRED,
            FSFetchReason::UPDATE_CONTEXT
        ));
        // Test flag status
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::FETCH_REQUIRED, $value);

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCHED, FSFetchReason::NONE));
        // Test flag status
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::FETCHED, $value);
    }

    public function testFlagNull()
    {
        $key = "key";

        $visitorDelegateMock = $this->getMockForAbstractClass(
            'Flagship\Visitor\VisitorAbstract',
            [
             'getFlagValue',
             'userExposed',
             'getFlagMetadata',
            ],
            '',
            false
        );
        $flag = new FSFlag($key, $visitorDelegateMock);

        $this->assertFalse($flag->exists());

        $visitorDelegateMock->expects($this->once())->method('getFlagMetadata')->willReturn(FSFlagMetadata::getEmpty());

        $metadataValue = $flag->getMetadata();

        $this->assertEquals($metadataValue, FSFlagMetadata::getEmpty());

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCHED, FSFetchReason::NONE));
        // Test flag status
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::NOT_FOUND, $value);
    }

    public function testFlagVisitorNull()
    {
        $key = "key";


        $flag = new FSFlag($key, null);

        $this->assertFalse($flag->exists());

        $metadataValue = $flag->getMetadata();

        $this->assertEquals($metadataValue, FSFlagMetadata::getEmpty());

        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::NOT_FOUND, $value);

        $flag->visitorExposed();

        $value = $flag->getValue("defaultValue");
        $this->assertEquals("defaultValue", $value);
    }
}
