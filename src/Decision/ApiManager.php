<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Model\Modification;
use Flagship\Traits\BuildApiTrait;
use Flagship\Traits\LogTrait;
use Flagship\Traits\ValidatorTrait;
use Flagship\Visitor;

/**
 * This class manage all http calls to Decision api
 *
 * @package Flagship\Decision
 */
class ApiManager extends DecisionManagerAbstract
{
    use ValidatorTrait;
    use LogTrait;
    use BuildApiTrait;


    /**
     * @inheritDoc
     */
    public function getCampaigns(Visitor $visitor)
    {
        try {
            $headers = $this->buildHeader($visitor->getConfig()->getApiKey());
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($visitor->getConfig()->getTimeOut());
            $url = $this->buildDecisionApiUrl($visitor->getConfig()->getEnvId() . '/' .
                FlagshipConstant::URL_CAMPAIGNS . '/');

            $postData = [
                "visitorId" => $visitor->getVisitorId(),
                "trigger_hit" => false,
                "context" => $visitor->getContext()
            ];

            $response = $this->httpClient->post($url, [FlagshipConstant::EXPOSE_ALL_KEYS => true], $postData);
            $body = $response->getBody();
            return $body [FlagshipField::FIELD_CAMPAIGNS];
        } catch (Exception $exception) {
            $this->logError($visitor->getConfig()->getLogManager(), $exception->getMessage());
        }
        return [];
    }

    /**
     * Return modification of a campaign
     *
     * @param  array $modificationValues
     * @param  $campaign
     * @param  array $modifications
     * @return array
     */
    private function getModificationValues(array $modificationValues, $campaign, $modifications)
    {
        $localModifications = [];
        foreach ($modificationValues as $key => $modificationValue) {
            if (!$this->isKeyValid($key)) {
                continue;
            }

            //check if the key is already used
            $modification = $this->checkModificationKeyExist($modifications, $key);
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
                $modification->setVariationGroupId($campaign[FlagshipField::FIELD_VARIATION_GROUP_ID]);
            }

            if (isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_ID])) {
                $modification->setVariationId(
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
                $localModifications[] = $modification;
            }
        }
        return array_merge($modifications, $localModifications);
    }

    /**
     * @inheritDoc
     */
    public function getModifications($campaigns)
    {

        $modifications = [];
        foreach ($campaigns as $campaign) {
            if (
                !isset($campaign[FlagshipField::FIELD_VARIATION])
                || !isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS])
                || !isset($campaign[FlagshipField::FIELD_VARIATION][FlagshipField::FIELD_MODIFICATIONS]
                    [FlagshipField::FIELD_VALUE])
            ) {
                continue;
            }

            $modificationValues = $campaign[FlagshipField::FIELD_VARIATION]
            [FlagshipField::FIELD_MODIFICATIONS][FlagshipField::FIELD_VALUE];

            $modifications = $this->getModificationValues($modificationValues, $campaign, $modifications);
        }
        return $modifications;
    }

    /**
     * @param  Modification[] $modifications
     * @param  $key
     * @return Modification|null
     */
    private function checkModificationKeyExist(array $modifications, $key)
    {
        foreach ($modifications as $modification) {
            if ($modification->getKey() === $key) {
                return $modification;
            }
        }
        return null;
    }
}
