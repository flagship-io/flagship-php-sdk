<?php

namespace Flagship\Flag;

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
            ->setVariationGroupId('varGroupId')
            ->setCampaignId("campaignId")
            ->setKey($key)->setValue("value")
            ->setSlug("slug")
            ->setCampaignType("ab");

        $metadata = new FlagMetadata(
            $flagDTO->getCampaignId(),
            $flagDTO->getVariationGroupId(),
            $flagDTO->getVariationId(),
            $flagDTO->getIsReference(),
            $flagDTO->getCampaignType(),
            $flagDTO->getSlug()
        );
        $visitorDelegateMock = $this->getMockForAbstractClass(
            'Flagship\Visitor\VisitorAbstract',
            [],
            "",
            false,
            false,
            true,
            ['getFlagValue','userExposed','getFlagMetadata', 'getFlagsDTO'],
            '',
            false
        );

        $visitorDelegateMock->method("getFlagsDTO")->willReturn([$flagDTO]);

        $visitorDelegateMock->expects($this->exactly(2))->method('getFlagValue')->withConsecutive(
            [ $key,
            $defaultValue,
            $flagDTO,
            true],
            [ $key,
               $defaultValue,
               $flagDTO,
               false]
        )->willReturn($flagDTO->getValue());

        $flag = new Flag($key, $visitorDelegateMock, $defaultValue);

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

        $flag->userExposed();

        $visitorDelegateMock->expects($this->once())->method('getFlagMetadata')->with(
            $key,
            $metadata,
            true
        )->willReturn($metadata);

        $metadataValue = $flag->getMetadata();

        $this->assertSame($metadataValue, $metadata);

        $this->assertSame($key, $flag->getKey());
        $this->assertSame($defaultValue, $flag->getDefaultValue());
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
            null
        );
        $visitorDelegateMock = $this->getMockForAbstractClass(
            'Flagship\Visitor\VisitorAbstract',
            ['getFlagValue','userExposed','getFlagMetadata'],
            '',
            false
        );
        $flag = new Flag($key, $visitorDelegateMock, $defaultValue, $metadata, null);

        $this->assertFalse($flag->exists());

        $visitorDelegateMock->expects($this->never())->method('getFlagMetadata');

        $metadataValue = $flag->getMetadata();

        $this->assertEquals($metadataValue, $metadata);
    }
}
