<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\FlagshipConfig;
use Flagship\Interfaces\ApiManagerInterface;
use Flagship\Interfaces\HttpClientInterface;
use Flagship\Visitor;

/**
 * This class manage all http calls to Decision api
 * @package Flagship\Decision
 */
class ApiManager implements ApiManagerInterface
{
    private static $instance;

    /**
     * @var FlagshipConfig
     */
    private $config;


    /**
     * Return ApiManager singleton instance
     * @param FlagshipConfig $config
     * @return ApiManager
     */
    public static function getInstance(FlagshipConfig $config)
    {
        if (is_null(self::$instance)) {
            self::$instance = new ApiManager($config);
        }
        return self::$instance;
    }

    /**
     * ApiManager constructor.
     * @param FlagshipConfig $config
     */
    private function __construct(FlagshipConfig $config)
    {
        $this->config = $config;
    }

    private function __clone()
    {
    }

    /**
     * @inheritDoc
     */
    public function getCampaigns(Visitor $visitor, HttpClientInterface $curl)
    {
        try {
            $headers = $this->buildHeader();
            $curl->setHeaders($headers);
            $curl->setTimeout($this->config->getTimeOut());
            $url = $this->buildDecisionApiUrl();
            $postData = $this->buildPostData($visitor);
            $response = $curl->post($url, [FlagshipConstant::EXPOSE_ALL_KEYS => true], $postData);
            return $response['campaigns'];
        } catch (Exception $e) {
            $this->log($e->getMessage());
            return [];
        }
    }


    /**
     * Build http request header
     * @return array
     */
    private function buildHeader()
    {
        return [
            'x-api-key' => $this->config->getApiKey(),
            'x-sdk-client' => FlagshipConstant::SDK_LANGUAGE,
            'x-sdk-version' => FlagshipConstant::SDK_VERSION,
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Build and return the Decision Api url
     * @return string
     */
    private function buildDecisionApiUrl()
    {
        return FlagshipConstant::BASE_API_URL . '/' . $this->config->getEnvId() . '/campaigns/';
    }

    /**
     * Build and return the http Post body according to visitor
     * @param Visitor $visitor
     * @return array
     */
    private function buildPostData(Visitor $visitor)
    {
        return [
            "visitorId" => $visitor->getVisitorId(),
            "trigger_hit" => false,
            "context" => $visitor->getContext()
        ];
    }

    /**
     * Report ApiManager Error
     * @param string $message
     * @param null $context
     */
    private function log($message = "Decision manager", $context = null)
    {
        $logManger = $this->config->getLogManager();
        if (!is_null($logManger)) {
            $logManger->error($message, $context);
        }
    }
}
