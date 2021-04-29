<?php


namespace Flagship\Api;


use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\Modification;
use Flagship\Traits\BuildApiTrait;
use Flagship\Traits\LogTrait;
use Flagship\Visitor;

class TrackingManager extends TrackingManagerAbstract
{
    use LogTrait;
    use BuildApiTrait;
    /**
     * @inheritDoc
     */
    public function sendActive(Visitor $visitor, Modification $modification)
    {
        try {
            $headers = $this->buildHeader($visitor->getConfig());
            $this->httpClient->setHeaders($headers);
            $this->httpClient->setTimeout($visitor->getConfig()->getTimeOut());
            $url = $this->buildDecisionApiUrl(FlagshipConstant::URL_ACTIVATE_MODIFICATION);
            $postData = [
                FlagshipConstant::VISITOR_ID => $visitor->getVisitorId(),
                FlagshipConstant::VARIATION_ID => $modification->getVariationId(),
                FlagshipConstant::VARIATION_GROUP_ID => $modification->getVariationGroupId(),
                FlagshipConstant::CUSTOMER_ENV_ID => $visitor->getConfig()->getEnvId()
            ];
            $this->httpClient->post($url, [], $postData);

        } catch (Exception $exception) {
            $this->logError($visitor->getConfig()->getLogManager(), $exception->getMessage());
        }
    }

}