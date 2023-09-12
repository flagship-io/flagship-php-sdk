<?php

namespace Flagship\Decision;

use DateTime;
use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Hit\Troubleshooting;
use Flagship\Model\TroubleshootingData;
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
    public function getCampaigns(VisitorAbstract $visitor)
    {
        $postData = [
            "visitorId" => $visitor->getVisitorId(),
            "anonymousId" => $visitor->getAnonymousId(),
            "trigger_hit" => false,
            "context" => count($visitor->getContext()) > 0 ? $visitor->getContext() : null,
            "visitor_consent" => $visitor->hasConsented()
        ];
        $headers = $this->buildHeader($this->getConfig()->getApiKey());
        $url = $this->buildDecisionApiUrl($this->getConfig()->getEnvId() . '/' .
            FlagshipConstant::URL_CAMPAIGNS . '?' .
            FlagshipConstant::EXPOSE_ALL_KEYS . "=true&extras[]=accountSettings");
        $now = $this->getNow();
        try {
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($this->getConfig()->getTimeout() / 1000);

            $response = $this->httpClient->post($url, [], $postData);
            $body = $response->getBody();
            $hasPanicMode = !empty($body["panic"]);

            $this->setIsPanicMode($hasPanicMode);
            $this->troubleshootingData = null;
            if (
                isset($body[FlagshipField::EXTRAS][FlagshipField::ACCOUNT_SETTINGS][FlagshipField::TROUBLESHOOTING])
            ) {
                $troubleshooting = $body[FlagshipField::EXTRAS][FlagshipField::ACCOUNT_SETTINGS]
                [FlagshipField::TROUBLESHOOTING];
                $startDate = new DateTime($troubleshooting[FlagshipField::START_DATE]);
                $endDate = new DateTime($troubleshooting[FlagshipField::END_DATE]);
                $troubleshootingData = new TroubleshootingData();
                $troubleshootingData->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setTimezone($troubleshooting[FlagshipField::TIMEZONE])
                    ->setTraffic($troubleshooting[FlagshipField::TRAFFIC]);
                $this->troubleshootingData = $troubleshootingData;
            }

            if (isset($body[FlagshipField::FIELD_CAMPAIGNS])) {
                return $body[FlagshipField::FIELD_CAMPAIGNS];
            }
        } catch (Exception $exception) {
            $this->logError($this->getConfig(), $exception->getMessage(), [
                FlagshipConstant::TAG => __FUNCTION__
            ]);

            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::GET_CAMPAIGNS_ROUTE_RESPONSE_ERROR)
                ->setLogLevel("ERROR")
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId())
                ->setVisitorContext($visitor->getContext())
                ->setVisitorInstanceId($visitor->getInstanceId())
                ->setFlagshipInstanceId($visitor->getFlagshipInstanceId())
                ->setTraffic(100)
                ->setConfig($this->getConfig())
                ->setHttpRequestBody($postData)
                ->setHttpRequestHeaders($headers)
                ->setHttpRequestMethod("POST")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($exception->getMessage())
                ->setHttpResponseTime($this->getNow() - $now)
            ;
            $visitor->sendTroubleshootingHit($troubleshooting);
        }
        return null;
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
