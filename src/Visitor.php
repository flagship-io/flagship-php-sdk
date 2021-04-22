<?php

namespace Flagship;

use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipField;
use Flagship\Interfaces\ApiManagerInterface;
use Flagship\Model\Modification;
use Flagship\Traits\LogTrait;
use Flagship\Utils\HttpClient;
use Flagship\Utils\Validator;

/**
 * Flagship visitor representation.
 * @package Flagship
 */
class Visitor
{
    use LogTrait;

    /**
     * @var FlagshipConfig
     */
    private $config;
    /**
     * @var string
     */
    private $visitorId;
    /**
     * @var array
     */
    private $context;

    /**
     * @var Modification[]
     */
    private $modifications;

    /**
     * @var ApiManagerInterface
     */
    private $decisionAPi;


    /**
     * Create a new visitor.
     * @param FlagshipConfig $config : configuration used when the visitor has been created.
     * @param string $visitorId : visitor unique identifier.
     * @param array $context : visitor context. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     */
    public function __construct($config, $visitorId, $context = [])
    {
        $this->decisionAPi = ApiManager::getInstance($config);
        $this->config = $config;
        $this->setVisitorId($visitorId);
        $this->updateContextCollection($context);
    }

    /**
     * @return FlagshipConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return Visitor
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * @param string $visitorId
     * @return Visitor
     */
    public function setVisitorId($visitorId)
    {
        if (!empty($visitorId)) {
            $this->visitorId = $visitorId;
        } else {
            $this->logError($this->config->getLogManager(), "");  //Log visitorId empty
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Clear the current context and set a new context value
     * @param array $context : collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     * @return Visitor
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context key must be String, and value type must be one of the following : Number, Boolean, String.
     *
     * @param string $key : context key.
     * @param int|float|string|bool $value : context value.
     */
    public function updateContext($key, $value)
    {
        if (!$this->isKeyValid($key) || !$this->isValueValid($value)) {
            $this->logError($this->config->getLogManager(), ""); // Error
            return;
        }
        $this->context[$key] = $value;
    }

    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context keys must be String, and values types must be one of the following : Number, Boolean, String.
     *
     * @param array $Context  collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     */
    public function updateContextCollection(array $Context)
    {
        foreach ($Context as $itemKey => $item) {
            $this->updateContext($itemKey, $item);
        }
    }

    /**
     * @return array
     */
    public function getModifications()
    {
        return $this->modifications;
    }

    /**
     * Retrieve a modification value by its key. If no modification match the given
     * key or if the stored value type and default value type do not match, default value will be returned.
     * @param string $key : key associated to the modification.
     * @param string|bool|numeric $defaultValue : default value to return.
     * @param bool $activate : Set this parameter to true to automatically report on our server that the
     * current visitor has seen this modification. It is possible to call activateModification() later.
     * @return string|bool|numeric : modification value or default value.
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        if (!$this->isKeyValid($key)) {
            return $defaultValue;
        }
        foreach ($this->modifications as $modification) {
            if (
                $modification->getKey() === $key &&
                gettype($modification->getValue()) === gettype($defaultValue)
            ) {
                return $modification->getValue();
            }
        }
        return  $defaultValue;
    }

    /**
     * Get the campaign modification information value matching the given key.
     * @param $key : key which identify the modification.
     * @return string|null JSON encoded string containing the modification information.
     */
    public function getModificationInfo($key)
    {
        if (!$this->isKeyValid($key)) {
            return null;
        }
        foreach ($this->modifications as $modification) {
            if ($modification->getKey() === $key) {
                return $this->parseCampaignToJson($modification);
            }
        }
        return null;
    }

    /**
     * Return a JSON encoded string of Campaign from modification
     * @param Modification $modification Modification containing information
     * @return string JSON encoded string
     */
    private function parseCampaignToJson(Modification $modification)
    {
        $campaign = [
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference()
        ];
        return json_encode($campaign);
    }

    /**
     * This function will call the decision api and update all the campaigns modifications
     * from the server according to the visitor context.
     */
    public function synchronizedModifications()
    {
        $this->modifications = $this->decisionAPi->getCampaignsModifications($this, HttpClient::create());
    }

    /**
     * Return true if the key is not null and is a string, otherwise return false
     * @param mixed $key Context key
     * @return bool
     */
    private function isKeyValid($key)
    {
        $check = Validator::isKeyValid($key);
        if (!$check) {
            $this->logError($this->config->getLogManager(), ''); // Log
        }
        return $check;
    }


    /**
     * Return true if the value is not null and is a number or a boolean or a string,
     * otherwise return false
     * @param $value
     * @return bool
     */
    private function isValueValid($value)
    {
        $check = Validator::isValueValid($value);
        if (!$check) {
            $this->logError($this->config->getLogManager(), '');// log
        }
        return $check;
    }
}
