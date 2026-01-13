<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;
use Flagship\Visitor\StrategyAbstract;

class VisitorCacheDataDTOTest extends TestCase
{
    public function testConstructor()
    {
        $dto = new VisitorCacheDataDTO('visitor123', 'anon456');

        $this->assertSame('visitor123', $dto->getVisitorId());
        $this->assertSame('anon456', $dto->getAnonymousId());
    }

    public function testConstructorWithNullAnonymousId()
    {
        $dto = new VisitorCacheDataDTO('visitor123', null);

        $this->assertSame('visitor123', $dto->getVisitorId());
        $this->assertNull($dto->getAnonymousId());
    }

    public function testGettersAndSetters()
    {
        $dto = new VisitorCacheDataDTO('visitor1', null);

        $instance1 = $dto->setVisitorId('visitor2');
        $this->assertSame('visitor2', $dto->getVisitorId());
        $this->assertSame($dto, $instance1);

        $instance2 = $dto->setAnonymousId('anon123');
        $this->assertSame('anon123', $dto->getAnonymousId());
        $this->assertSame($dto, $instance2);

        $instance3 = $dto->setConsent(true);
        $this->assertTrue($dto->getConsent());
        $this->assertSame($dto, $instance3);

        $context = ['browser' => 'chrome', 'version' => '120'];
        $instance4 = $dto->setContext($context);
        $this->assertSame($context, $dto->getContext());
        $this->assertSame($dto, $instance4);

        $history = ['campaign1' => 'variation1'];
        $instance5 = $dto->setAssignmentsHistory($history);
        $this->assertSame($history, $dto->getAssignmentsHistory());
        $this->assertSame($dto, $instance5);

        $campaigns = [
            new CampaignCacheDTO('c1', 'vg1', 'v1', new ModificationsDTO('ab', []))
        ];
        $instance6 = $dto->setCampaigns($campaigns);
        $this->assertSame($campaigns, $dto->getCampaigns());
        $this->assertSame($dto, $instance6);
    }

    public function testFromArray()
    {
        $array = [
            StrategyAbstract::VISITOR_ID => 'visitor123',
            StrategyAbstract::ANONYMOUS_ID => 'anon456',
            StrategyAbstract::CONSENT => true,
            StrategyAbstract::CONTEXT => ['key1' => 'value1', 'key2' => 123],
            StrategyAbstract::ASSIGNMENTS_HISTORY => ['c1' => 'v1'],
            StrategyAbstract::CAMPAIGNS => [
                [
                    StrategyAbstract::CAMPAIGN_ID => 'c1',
                    StrategyAbstract::VARIATION_GROUP_ID => 'vg1',
                    StrategyAbstract::VARIATION_ID => 'v1',
                    'flags' => [
                        'type' => 'ab',
                        'value' => []
                    ]
                ]
            ]
        ];

        $dto = VisitorCacheDataDTO::fromArray($array);

        $this->assertSame('visitor123', $dto->getVisitorId());
        $this->assertSame('anon456', $dto->getAnonymousId());
        $this->assertTrue($dto->getConsent());
        $this->assertIsArray($dto->getContext());
        $this->assertIsArray($dto->getAssignmentsHistory());
        $this->assertIsArray($dto->getCampaigns());
    }

    public function testFromArrayWithMissingFields()
    {
        $dto = VisitorCacheDataDTO::fromArray([]);

        $this->assertSame('', $dto->getVisitorId());
        $this->assertNull($dto->getAnonymousId());
        $this->assertNull($dto->getConsent());
        $this->assertNull($dto->getContext());
    }

    public function testFromArrayWithInvalidTypes()
    {
        $array = [
            StrategyAbstract::VISITOR_ID => 123,
            StrategyAbstract::ANONYMOUS_ID => [],
            StrategyAbstract::CONSENT => 'true',
            StrategyAbstract::CONTEXT => 'not an array',
            StrategyAbstract::ASSIGNMENTS_HISTORY => 'invalid',
            StrategyAbstract::CAMPAIGNS => 'invalid'
        ];

        $dto = VisitorCacheDataDTO::fromArray($array);

        $this->assertSame('', $dto->getVisitorId());
        $this->assertNull($dto->getAnonymousId());
        $this->assertNull($dto->getConsent());
    }

    public function testFromArrayFiltersInvalidContextValues()
    {
        $array = [
            StrategyAbstract::VISITOR_ID => 'visitor123',
            StrategyAbstract::ANONYMOUS_ID => null,
            StrategyAbstract::CONTEXT => [
                'valid1' => 'string',
                'valid2' => 123,
                'invalid1' => ['array'],
                'invalid2' => new \stdClass(),
            ]
        ];

        $dto = VisitorCacheDataDTO::fromArray($array);
        $context = $dto->getContext();

        $this->assertArrayHasKey('valid1', $context);
        $this->assertArrayHasKey('valid2', $context);
        $this->assertArrayNotHasKey('invalid1', $context);
        $this->assertArrayNotHasKey('invalid2', $context);
    }

    public function testToArray()
    {
        $dto = new VisitorCacheDataDTO('visitor123', 'anon456');
        $dto->setConsent(true);
        $dto->setContext(['key' => 'value']);
        $dto->setAssignmentsHistory(['c1' => 'v1']);
        $dto->setCampaigns([
            new CampaignCacheDTO('c1', 'vg1', 'v1', new ModificationsDTO('ab', []))
        ]);

        $array = $dto->toArray();

        $this->assertSame('visitor123', $array[StrategyAbstract::VISITOR_ID]);
        $this->assertSame('anon456', $array[StrategyAbstract::ANONYMOUS_ID]);
        $this->assertTrue($array[StrategyAbstract::CONSENT]);
        $this->assertIsArray($array[StrategyAbstract::CONTEXT]);
        $this->assertIsArray($array[StrategyAbstract::ASSIGNMENTS_HISTORY]);
        $this->assertIsArray($array[StrategyAbstract::CAMPAIGNS]);
    }

    public function testToArrayWithNullValues()
    {
        $dto = new VisitorCacheDataDTO('visitor123', 'anon456');

        $array = $dto->toArray();

        $this->assertArrayHasKey(StrategyAbstract::VISITOR_ID, $array);
        $this->assertArrayHasKey(StrategyAbstract::ANONYMOUS_ID, $array);
        $this->assertArrayNotHasKey(StrategyAbstract::CONSENT, $array);
        $this->assertArrayNotHasKey(StrategyAbstract::CONTEXT, $array);
    }

    public function testRoundTripConversion()
    {
        $originalArray = [
            StrategyAbstract::VISITOR_ID => 'visitor123',
            StrategyAbstract::ANONYMOUS_ID => 'anon456',
            StrategyAbstract::CONSENT => true,
            StrategyAbstract::CONTEXT => ['key' => 'value'],
            StrategyAbstract::ASSIGNMENTS_HISTORY => ['c1' => 'v1']
        ];

        $dto = VisitorCacheDataDTO::fromArray($originalArray);
        $resultArray = $dto->toArray();

        $this->assertSame($originalArray[StrategyAbstract::VISITOR_ID], $resultArray[StrategyAbstract::VISITOR_ID]);
        $this->assertSame($originalArray[StrategyAbstract::ANONYMOUS_ID], $resultArray[StrategyAbstract::ANONYMOUS_ID]);
        $this->assertSame($originalArray[StrategyAbstract::CONSENT], $resultArray[StrategyAbstract::CONSENT]);
    }
}
