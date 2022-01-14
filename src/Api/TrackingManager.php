<?php

namespace Flagship\Api;

use Exception;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FlagDTO;
use Flagship\Traits\BuildApiTrait;
use Flagship\Traits\LogTrait;
use Flagship\Visitor\VisitorAbstract;

/**
 * Class TrackingManager
 * @package Flagship\Api
 */
class TrackingManager extends TrackingManagerAbstract
{
    use LogTrait;
    use BuildApiTrait;

    /**
     * @inheritDoc
     */
    public function sendActive(VisitorAbstract $visitor, FlagDTO $modification)
    {
        try {
            $headers = $this->buildHeader($visitor->getConfig()->getApiKey());
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($visitor->getConfig()->getTimeout() / 1000);
            $url = $this->buildDecisionApiUrl(FlagshipConstant::URL_ACTIVATE_MODIFICATION);
            $postData = [
                FlagshipConstant::VISITOR_ID_API_ITEM => $visitor->getVisitorId(),
                FlagshipConstant::VARIATION_ID_API_ITEM => $modification->getVariationId(),
                FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $modification->getVariationGroupId(),
                FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $visitor->getConfig()->getEnvId()
            ];

            $postData = $this->setVisitorBodyParams(
                $visitor->getVisitorId(),
                $visitor->getAnonymousId(),
                $postData,
                FlagshipConstant::ANONYMOUS_ID
            );

            $response = $this->httpClient->post($url, [], $postData);
            return $response->getStatusCode() == 204;
        } catch (Exception $exception) {
            $this->logError($visitor->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit)
    {
        try {
            $headers = $this->buildHeader($hit->getConfig()->getApiKey());
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($hit->getConfig()->getTimeOut() / 1000);
            $url = FlagshipConstant::HIT_API_URL;
            $this->httpClient->post($url, [], $hit->toArray());
        } catch (Exception $exception) {
            $this->logError($hit->getConfig(), $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }
    }

    public function sendConsentHit(VisitorAbstract $visitor, FlagshipConfig $config)
    {
        try {
            $headers = $this->buildHeader($config->getApiKey());
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($config->getTimeOut() / 1000);
            $url = FlagshipConstant::HIT_CONSENT_URL;
            $postBody = [
                FlagshipConstant::T_API_ITEM => HitType::EVENT,
                FlagshipConstant::EVENT_LABEL_API_ITEM =>
                    FlagshipConstant::SDK_LANGUAGE . ":" . ($visitor->hasConsented() ? "true" : "false"),
                FlagshipConstant::EVENT_ACTION_API_ITEM => "fs_content",
                FlagshipConstant::EVENT_CATEGORY_API_ITEM => EventCategory::USER_ENGAGEMENT,
                FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
                FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP
            ];
            $postBody = $this->setVisitorBodyParams(
                $visitor->getVisitorId(),
                $visitor->getAnonymousId(),
                $postBody
            );
            $this->httpClient->post($url, [], $postBody);
        } catch (Exception $exception) {
            $this->logError($config, $exception->getMessage(), [FlagshipConstant::TAG => __FUNCTION__]);
        }
    }
}
