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
            ->setKey($key)->setValue("value");

        $metadata = new FlagMetadata(
            $flagDTO->getCampaignId(),
            $flagDTO->getVariationGroupId(),
            $flagDTO->getVariationId(),
            $flagDTO->getIsReference(),
            ""
        );
        $visitorDelegateMock = $this->getMockForAbstractClass(
            'Flagship\Visitor\VisitorAbstract',
            ['getFlagValue','userExposed','getFlagMetadata'],
            '',
            false
        );
        $flag = new Flag($key, $visitorDelegateMock, $defaultValue, $metadata, $flagDTO);

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

        $value = $flag->getValue();
        $this->assertEquals($value, $flagDTO->getValue());

        $value = $flag->getValue(false);
        $this->assertEquals($value, $flagDTO->getValue());

        $this->assertTrue($flag->exists());

        $visitorDelegateMock->expects($this->once())->method('userExposed')->with(
            $key,
            true,
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
            ""
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

        $this->assertSame($metadataValue, $metadata);
    }
}
