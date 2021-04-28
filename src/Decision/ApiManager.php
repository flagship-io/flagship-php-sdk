<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Model\Modification;
use Flagship\Traits\LogTrait;
use Flagship\Traits\ValidatorTrait;
use Flagship\Visitor;

/**
 * This class manage all http calls to Decision api
 *
 * @package Flagship\Decision
 */
class ApiManager extends ApiManagerAbstract
{
    use ValidatorTrait;
    use LogTrait;

    /**
     * @inheritDoc
     */
    public function sendActiveModification(Visitor $visitor, Modification $modification)
    {
        try {
            $headers = $this->buildHeader();
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($this->config->getTimeOut());
            $url = $this->buildDecisionApiUrl( FlagshipConstant::URL_ACTIVATE_MODIFICATION);
            $postData = [
                FlagshipConstant::VISITOR_ID => $visitor->getVisitorId(),
                FlagshipConstant::VARIATION_ID => $modification->getVariationId(),
                FlagshipConstant::VARIATION_GROUP_ID => $modification->getVariationGroupId(),
                FlagshipConstant::CUSTOMER_ENV_ID => $this->config->getEnvId()
            ];
            $this->httpClient->post($url, [], $postData);
        } catch (Exception $exception) {
            $this->logError($this->config->getLogManager(), $exception->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function getCampaigns(Visitor $visitor)
    {
        try {
            $headers = $this->buildHeader();
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($this->config->getTimeOut());
            $url = $this->buildDecisionApiUrl($this->config->getEnvId() . '/' . FlagshipConstant::URL_CAMPAIGNS . '/');

            $postData = [
                "visitorId" => $visitor->getVisitorId(),
                "trigger_hit" => false,
                "context" => $visitor->getContext()
            ];

            $response = $this->httpClient->post($url, [FlagshipConstant::EXPOSE_ALL_KEYS => true], $postData);
            return $response[FlagshipField::FIELD_CAMPAIGNS];
        } catch (Exception $exception) {
            $this->logError($this->config->getLogManager(), $exception->getMessage());
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCampaignsModifications(Visitor $visitor)
    {
        $campaigns = $this->getCampaigns($visitor);
        return $this->getAllModifications($campaigns);
    }

    /**
     *  Return an array of modification from all campaigns
     * @param array $campaigns
     * @return Modification[] Return an array of Modification
     */
    private function getAllModifications(array $campaigns)
    {
        $modifications = [];
        foreach ($campaigns as $campaign) {
            if (isset($campaign[FlagshipField::FIELD_VARIATION])) {
                if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS])) {
                    if (
                    isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS]
                        [FlagshipField::FIELD_VALUE])
                    ) {
                        $modificationValues = $campaign[FlagshipField::FIELD_VARIATION]
                        [FlagshipField::FIELD_MODIFICATIONS][FlagshipField::FIELD_VALUE];

                        foreach ($modificationValues as $key => $modificationValue) {
                            if ($this->isKeyValid($key)) {
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
        return $modifications;
    }

    /**
     * @param Modification[] $modifications
     * @param  $key
     * @return Modification|null
     */
    private function checkKeyExist(array $modifications, $key)
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
            'x-api-key' => $this->config->getApiKey(),'x-sdk-version' => FlagshipConstant::SDK_VERSION,
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
