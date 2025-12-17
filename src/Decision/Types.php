<?php

namespace Flagship\Decision;

/**
 * Centralized type definitions for Flagship SDK DTOs
 * 
 * These type aliases provide PHPStan-level type safety for array structures
 * 
 * @phpstan-type CampaignHttpHeader array{X-Api-Key: string|null, X-SDK-Version: string, Content-Type: string, X-SDK-Client: string}
 */
final class Types
{
    private function __construct() {}
}
