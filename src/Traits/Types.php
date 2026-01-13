<?php

namespace Flagship\Traits;

/**
 * Centralized type definitions for Flagship SDK DTOs
 * 
 * These type aliases provide PHPStan-level type safety for array structures
 * 
 * @phpstan-type BuildHeaderArray array{x-api-key: string, x-sdk-version: string, Content-Type: string, x-sdk-client: string}
 */
final class Types
{
    private function __construct() {}
}
