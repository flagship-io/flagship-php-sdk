<?php

namespace Flagship\Traits;

use Flagship\Enum\FlagshipConstant;

/**
 * @phpstan-import-type BuildHeaderArray from Types
 */
trait BuildApiTrait
{
    /**
     * Build http request header
     *
     * @param string $apiKey
     * @return BuildHeaderArray
     */
    protected function buildHeader(?string $apiKey): array
    {
        return [
            FlagshipConstant::HEADER_X_API_KEY     => $apiKey??'',
            FlagshipConstant::HEADER_X_SDK_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::HEADER_CONTENT_TYPE  => FlagshipConstant::HEADER_APPLICATION_JSON,
            FlagshipConstant::HEADER_X_SDK_CLIENT  => FlagshipConstant::SDK_LANGUAGE,
        ];
    }

    /**
     * Build and return the Decision Api url
     *
     * @param string $url
     * @return string
     */
    protected function buildDecisionApiUrl(string $url): string
    {
        return FlagshipConstant::BASE_API_URL . '/' . $url;
    }
}
