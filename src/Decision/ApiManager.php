<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\FlagshipConfig;
use Flagship\Interfaces\ApiManagerInterface;
use Flagship\Interfaces\HttpClientInterface;
use Flagship\Model\Modification;
use Flagship\Utils\Validator;
use Flagship\Visitor;

/**
 * This class manage all http calls to Decision api
 *
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
     *
     * @param  FlagshipConfig $config
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
     *
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
    public function getCampaigns(Visitor $visitor, HttpClientInterface $httpClient)
    {
        try {
            $headers = $this->buildHeader();
            $httpClient->setHeaders($headers);
            $httpClient->setTimeout($this->config->getTimeOut());
            $url = $this->buildDecisionApiUrl();
            $postData = $this->buildPostData($visitor);
            $response = $httpClient->post($url, [FlagshipConstant::EXPOSE_ALL_KEYS => true], $postData);
            return $response[FlagshipField::FIELD_CAMPAIGNS];
        } catch (Exception $e) {
            $this->log($e->getMessage());
            return [];
        }
    }

    /**
     * @param  Visitor             $visitor
     * @param  HttpClientInterface $httpClient
     * @return Modification[]
     */
    public function getCampaignsModifications(Visitor $visitor, HttpClientInterface $httpClient)
    {
        $campaigns = $this->getCampaigns($visitor, $httpClient);
        return $this->getAllModifications($campaigns);
    }

    /**
     * @param  array $campaigns
     * @return Modification[]
     */
    public function getAllModifications(array $campaigns)
    {
        $modifications = [];
        foreach ($campaigns as $campaign) {
            if (isset($campaign[FlagshipField::FIELD_VARIATION])) {
                if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS])) {
                    if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS][FlagshipField::FIELD_VALUE])
                    ) {
                        $modificationValues = $campaign[FlagshipField::FIELD_VARIATION]
                        [FlagshipField::FIELD_MODIFICATIONS][FlagshipField::FIELD_VALUE];

                        foreach ($modificationValues as $key => $modificationValue) {
                            if (Validator::isKeyValid($key)) {
                                //check if the key is already used
                                $modification = $this->checkKeyExist($modifications, $key);
                                $isKeyUsed = true;

                                if (is_null($modification)) {
                                    $modification = new Modification();
                                    $isKeyUsed = false;
                                }

                                $modification->setKey($key);
                                $modification->setValue($modificationValue);

                                if (isset($campaign[FlagshipField::FIELD_ID])) {
                                    $modification->setCampaignId($campaign[FlagshipField::FIELD_ID]);
                                }

                                if (isset($campaign[FlagshipField::FIELD_VARIATION_GROUP_ID])) {
                                    $modification
                                        ->setVariationGroupId($campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]);
                                }

                                if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_ID])) {
                                    $modification
                                        ->setVariationId(
                                            $campaign[FlagshipField::FIELD_VARIATION]
                                            [FlagshipField::FIELD_ID]
                                        );
                                }

                                if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_REFERENCE])) {
                                    $modification->setIsReference(
                                        $campaign[FlagshipField::FIELD_VARIATION]
                                        [FlagshipField::FIELD_REFERENCE]
                                    );
                                }

                                if (!$isKeyUsed) {
                                    $modifications[] = $modification;
                                }
                            }
                        }
                    }
                }
            }
        }
        return  $modifications;
    }

    /**
     * @param  Modification[] $modifications
     * @param  $key
     * @return Modification|null
     */
    private function checkKeyExist($modifications, $key)
    {
        foreach ($modifications as $modification) {
            if ($modification->getKey() === $key) {
                return $modification;
            }
        }
        return null;
    }

    /**
     * Build http request header
     *
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
     *
     * @return string
     */
    private function buildDecisionApiUrl()
    {
        return FlagshipConstant::BASE_API_URL . '/' . $this->config->getEnvId() . '/campaigns/';
    }

    /**
     * Build and return the http Post body according to visitor
     *
     * @param  Visitor $visitor
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
     *
     * @param string $message
     * @param null   $context
     */
    private function log($message = "Decision manager", $context = null)
    {
        $logManger = $this->config->getLogManager();
        if (!is_null($logManger)) {
            $logManger->error($message, $context);
        }
    }
}
