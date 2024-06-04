<?php

namespace Flagship\Flag;

use Flagship\Enum\FSFetchReason;
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
        $flagDTO->setIsReference(true)
            ->setVariationId("variationId")
            ->setVariationName("variationName")
            ->setVariationGroupId('varGroupId')
            ->setVariationGroupName("variationGroupName")
            ->setCampaignId("campaignId")
            ->setCampaignName("campaignName")
            ->setKey($key)->setValue("value")
            ->setSlug("slug")
            ->setCampaignType("ab");

        $metadata = new FlagMetadata(
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
            ['getFlagValue', 'userExposed', 'getFlagMetadata', 'getFlagsDTO'],
            '',
            false
        );

        $visitorDelegateMock->method("getFlagsDTO")->willReturn([$flagDTO]);

        $visitorDelegateMock->expects($this->exactly(2))->method('getFlagValue')->withConsecutive(
            [
                $key,
                $defaultValue,
                $flagDTO,
                true
            ],
            [
                $key,
                $defaultValue,
                $flagDTO,
                false
            ]
        )->willReturn($flagDTO->getValue());

        $flag = new FSFlag($key, $visitorDelegateMock, $defaultValue);

        $value = $flag->getValue();
        $this->assertEquals($value, $flagDTO->getValue());

        $value = $flag->getValue(false);
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
            $metadata,
            true
        )->willReturn($metadata);

        $metadataValue = $flag->getMetadata();

        $this->assertSame($metadataValue, $metadata);

        $this->assertSame($key, $flag->getKey());
        $this->assertSame($defaultValue, $flag->getDefaultValue());

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(FSFlagStatus::PANIC, FSFetchReason::NONE));
        // Test flag status 
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::PANIC, $value);

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(FSFlagStatus::FETCH_REQUIRED, FSFetchReason::UPDATE_CONTEXT));
        // Test flag status 
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::FETCH_REQUIRED, $value);

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(FSFlagStatus::FETCHED, FSFetchReason::NONE));
        // Test flag status 
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::FETCHED, $value);

    }

    public function testFlagNull()
    {
        $key = "key";
        $defaultValue = "DefaultValue";

        $metadata = new FlagMetadata(
            "",
            "",
            "",
            "",
            "",
            null,
            "",
            "",
            ""
        );
        $visitorDelegateMock = $this->getMockForAbstractClass(
            'Flagship\Visitor\VisitorAbstract',
            ['getFlagValue', 'userExposed', 'getFlagMetadata'],
            '',
            false
        );
        $flag = new FSFlag($key, $visitorDelegateMock, $defaultValue, $metadata, null);

        $this->assertFalse($flag->exists());

        $visitorDelegateMock->expects($this->never())->method('getFlagMetadata');

        $metadataValue = $flag->getMetadata();

        $this->assertEquals($metadataValue, $metadata);

        $visitorDelegateMock->setFetchStatus(new FetchFlagsStatus(FSFlagStatus::FETCHED, FSFetchReason::NONE));
        // Test flag status 
        $value = $flag->getStatus();
        $this->assertEquals(FSFlagStatus::NOT_FOUND, $value);
    }
}
