<?php

namespace Flagship\Decision;

use DateTime;
use Exception;
use Flagship\Enum\LogLevel;
use Flagship\Enum\FlagshipField;
use Flagship\Enum\FSFetchStatus;
use Flagship\Enum\FSFetchReason;
use Flagship\Hit\Troubleshooting;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\FetchFlagsStatus;
use Flagship\Visitor\VisitorAbstract;
use Flagship\Enum\TroubleshootingLabel;
use Flagship\Model\TroubleshootingData;

/**
 * This class manage all http calls to Decision api
 *
 * @package Flagship\Decision
 */
class ApiManager extends DecisionManagerAbstract
{
    /**
     * @throws Exception
     */
    protected function setTroubleshootingData(?array $body): void
    {
        $this->troubleshootingData = null;
        if (
            $body === null || !isset($body[FlagshipField::EXTRAS]) ||
            !isset($body[FlagshipField::EXTRAS][FlagshipField::ACCOUNT_SETTINGS]) ||
            !isset($body[FlagshipField::EXTRAS][FlagshipField::ACCOUNT_SETTINGS][FlagshipField::TROUBLESHOOTING])
        ) {
            return;
        }
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

    public function getCampaigns(VisitorAbstract $visitor): array|null
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

            $this->setTroubleshootingData($body);

            return $body[FlagshipField::FIELD_CAMPAIGNS] ?? null;
        } catch (Exception $exception) {
            $visitor->setFetchStatus(new FetchFlagsStatus(FSFetchStatus::FETCH_REQUIRED, FSFetchReason::FETCH_ERROR));
            $this->logError($this->getConfig(), $exception->getMessage(), [
                FlagshipConstant::TAG => __FUNCTION__
            ]);

            $troubleshooting = new Troubleshooting();
            $troubleshooting->setLabel(TroubleshootingLabel::GET_CAMPAIGNS_ROUTE_RESPONSE_ERROR)
                ->setHttpRequestBody($postData)
                ->setHttpRequestHeaders($headers)
                ->setHttpRequestMethod("POST")
                ->setHttpRequestUrl($url)
                ->setHttpResponseBody($exception->getMessage())
                ->setHttpResponseTime($this->getNow() - $now)
                ->setVisitorContext($visitor->getContext())
                ->setLogLevel(LogLevel::ERROR)
                ->setVisitorSessionId($visitor->getInstanceId())
                ->setFlagshipInstanceId($visitor->getFlagshipInstanceId())
                ->setTraffic(100)
                ->setConfig($this->getConfig())
                ->setVisitorId($visitor->getVisitorId())
                ->setAnonymousId($visitor->getAnonymousId());
            $visitor->sendTroubleshootingHit($troubleshooting);
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getCampaignFlags(VisitorAbstract $visitor): array
    {
        $campaigns = $this->getCampaigns($visitor);
        return $this->getFlagsData($campaigns);
    }
}
