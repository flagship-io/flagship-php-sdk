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
    private function buildHeader($apiKey)
    {
        return [
            'x-api-key' => $apiKey,'x-sdk-version' => FlagshipConstant::SDK_VERSION,
            'Content-Type' => 'application/json','x-sdk-client' => FlagshipConstant::SDK_LANGUAGE,
        ];
    }

    /**
     * Build and return the Decision Api url
     *
     * @return string
     */
    private function buildDecisionApiUrl($url)
    {
        return FlagshipConstant::BASE_API_URL . '/' . $url;
    }
}