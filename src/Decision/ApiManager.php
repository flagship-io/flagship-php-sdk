<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor\VisitorAbstract;

/**
 * This class manage all http calls to Decision api
 *
 * @package Flagship\Decision
 */
class ApiManager extends DecisionManagerAbstract
{
    /**
     * This function will fetch campaigns modifications from the server according to the visitor context and
     * return an associative array of campaigns
     *
     * @param VisitorAbstract $visitor
     * @return array return an associative array of campaigns
     */
    protected function getCampaigns(VisitorAbstract $visitor)
    {
        try {
            $headers = $this->buildHeader($this->getConfig()->getApiKey());
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($this->getConfig()->getTimeout() / 1000);
            $url = $this->buildDecisionApiUrl($this->getConfig()->getEnvId() . FlagshipConstant::URL_CAMPAIGNS);

            $postData = [
                "visitorId" => $visitor->getVisitorId(),
                "anonymousId" => $visitor->getAnonymousId(),
                "trigger_hit" => false,
                "context" => count($visitor->getContext()) > 0 ? $visitor->getContext() : null
            ];
            $query = [FlagshipConstant::EXPOSE_ALL_KEYS => "true"];

            if (!$visitor->hasConsented()) {
                $query[FlagshipConstant::SEND_CONTEXT_EVENT] = "false";
            }
            $response = $this->httpClient->post($url, $query, $postData);
            $body = $response->getBody();
            $hasPanicMode = !empty($body["panic"]);

            $this->setIsPanicMode($hasPanicMode);

            if (isset($body[FlagshipField::FIELD_CAMPAIGNS])) {
                return $body[FlagshipField::FIELD_CAMPAIGNS];
            }
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [
                FlagshipConstant::TAG => __FUNCTION__
            ]);
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCampaignModifications(VisitorAbstract $visitor)
    {
        $campaigns = $this->getCampaigns($visitor);
        return $this->getModifications($campaigns);
    }
}
