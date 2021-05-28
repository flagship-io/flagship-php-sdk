<?php

namespace Flagship\Traits;

use Flagship\Enum\FlagshipConstant;

trait BuildApiTrait
{

    /**
     * Build http request header
     *
     * @return array
     */
    protected function buildHeader($apiKey)
    {
        return [
            FlagshipConstant::HEADER_X_API_KEY => $apiKey,
            FlagshipConstant::HEADER_X_SDK_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::HEADER_CONTENT_TYPE => FlagshipConstant::HEADER_APPLICATION_JSON,
            FlagshipConstant::HEADER_X_SDK_CLIENT => FlagshipConstant::SDK_LANGUAGE,
        ];
    }

    /**
     * Build and return the Decision Api url
     *
     * @return string
     */
    protected function buildDecisionApiUrl($url)
    {
        return FlagshipConstant::BASE_API_URL . '/' . $url;
    }
}
