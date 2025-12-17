<?php

namespace Flagship\Model;

/**
 * Centralized type definitions for Flagship SDK DTOs
 * 
 * These type aliases provide PHPStan-level type safety for array structures
 * used throughout the SDK. They have no runtime impact.
 * 
 * @phpstan-type ModificationsArray array{type: string, value: mixed}
 * @phpstan-type VariationArray array{id: string, name: string|null, reference: bool|null, modifications: ModificationsArray}
 * @phpstan-type CampaignArray array{id: string, name: string|null, slug: string|null, variationGroupId: string, variationGroupName: string|null, variation: VariationArray, type: string|null}
 * @phpstan-type CampaignCacheArray array{slug?: string|null, name?: string, campaignId: string, variationGroupId: string, variationId: string, isReference?: bool, type: string|null, activated?: bool, flags?: ModificationsArray}
 * @phpstan-type VisitorCacheDataArray array{visitorId: string, anonymousId: string|null, consent?: bool, context?: array<string, scalar>, assignmentsHistory?: array<string, string>, campaigns?: array<CampaignCacheArray>}
 * @phpstan-type VisitorCacheArray array{version: int, data: VisitorCacheDataArray}
 * @phpstan-type FlagValue array<array-key,array<mixed>|bool|float|int|string|null>
 * @phpstan-type TargetingsArray array{operator: string, key: string, value: mixed}
 * @phpstan-type TargetingGroupArray array{targetings: array<TargetingsArray>}
 * @phpstan-type TargetingArray array{targetingGroups: array<TargetingGroupArray>}
 * @phpstan-type BucketingVariationArray array{id: string, name: string|null, modifications: ModificationsArray, allocation: float|null, reference: bool|null}
 * @phpstan-type VariationGroupArray array{id: string, name?: string, targeting: TargetingArray, variations: array<BucketingVariationArray>}
 * @phpstan-type TroubleshootingArray array{startDate: string, endDate: string, traffic: float, timezone: string}
 * @phpstan-type AccountSettingsArray array{enabledXPC?: bool, troubleshooting?: TroubleshootingArray, eaiCollectEnabled?: bool, eaiActivationEnabled?: bool}
 * @phpstan-type BucketingCampaignArray array{id: string, name?: string, type: string, slug?: string|null, variationGroups: array<VariationGroupArray>}
 * @phpstan-type BucketingArray array{panic?: bool, campaigns?: array<BucketingCampaignArray>, accountSettings?: AccountSettingsArray}
 * @phpstan-type HitCacheDataArray array{visitorId: string, anonymousId?: string|null, type: string, time: float|null, content: array<string, mixed>}
 * @phpstan-type HitCacheArray array{version: int, data: HitCacheDataArray}
 */
final class Types
{
    private function __construct() {}
}
