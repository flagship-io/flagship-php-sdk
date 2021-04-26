<?php

namespace Flagship;

use Flagship\Decision\ApiManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Interfaces\ApiManagerInterface;
use Flagship\Model\Modification;
use Flagship\Traits\LogTrait;
use Flagship\Utils\HttpClient;
use Flagship\Utils\Validator;

/**
 * Flagship visitor representation.
 *
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
    private $modifications = [];

    /**
     * @var ApiManagerInterface
     */
    private $decisionAPi;


    /**
     * Create a new visitor.
     *
     * @param FlagshipConfig $config    : configuration used when the visitor has been created.
     * @param string         $visitorId : visitor unique identifier.
     * @param array          $context   : visitor context. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     */
    public function __construct($config, $visitorId, $context = [])
    // every context[] in the code should be a class I think, a simple array is a bit harder to understrand
    // imo, we can create a Context class with methods (add, get, contains...) like this ? https://github.com/wcomnisky/flagship-php-sdk/blob/develop/src/Context/Context.php
    // more, this class can extend a trait ContextAware to handle some automatic logic
    {
        $this->decisionAPi = ApiManager::getInstance($config);
        // dependancy injection is better than singleton pattern in 2021, what do you think ?
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
     * @param  FlagshipConfig $config
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
     * @param  string $visitorId
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
     *
     * @param  array $context : collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
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
     * @param string                $key   : context key.
     * @param int|float|string|bool $value : context value.
     */
    public function updateContext($key, $value)
    // if you have a Context class instead of an array I think we don't need this method ?
    {
        if (!Validator::isKeyValid($key) || !Validator::isValueValid($value)) {
            $this->logError(
                $this->config->getLogManager(),
                FlagshipConstant::CONTEXT_PARAM_ERROR,
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_UPDATE_CONTEXT]
            );

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
     * @param array $Context collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
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
     *
     * @param  string              $key          : key associated to the modification.
     * @param  string|bool|numeric $defaultValue : default value to return.
     * @param  bool                $activate     : Set this parameter to true
     * to automatically report on our server that the
     *                                           current visitor has seen this modification. It is possible to call
     *                                           activateModification() later.
     * @return string|bool|numeric : modification value or default value.
     */
    public function getModification($key, $defaultValue, $activate = false)
    {
        if (!Validator::isKeyValid($key)) {
            $this->logError(
                $this->config->getLogManager(),
                sprintf(FlagshipConstant::GET_MODIFICATION_KEY_ERROR, $key),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]
            );
            return $defaultValue;
        }
        $foundKey = false;
        foreach ($this->modifications as $modification) {
            if ($modification->getKey() === $key) {
                $foundKey = true;
                if (gettype($modification->getValue()) === gettype($defaultValue)) {
                    return $modification->getValue();
                }
                $this->logError(
                    $this->config->getLogManager(),
                    sprintf(FlagshipConstant::GET_MODIFICATION_CAST_ERROR, $key),
                    [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]
                );
                break;
            }
        }
        if (!$foundKey) {
            $this->logError(
                $this->config->getLogManager(),
                sprintf(FlagshipConstant::GET_MODIFICATION_MISSING_ERROR, $key),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION]
            );
        }
        return  $defaultValue;
    }

    /**
     * Get the campaign modification information value matching the given key.
     *
     * @param  $key : key which identify the modification.
     * @return array|null
     */
    public function getModificationInfo($key)
    {
        if (!Validator::isKeyValid($key)) {
            $this->logError(
                $this->config->getLogManager(),
                sprintf(FlagshipConstant::GET_MODIFICATION_INFO_ERROR, $key),
                [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION_INFO]
            );
            return null;
        }
        foreach ($this->modifications as $modification) {
            if ($modification->getKey() === $key) {
                return $this->parseToCampaign($modification);
            }
        }
        $this->logError(
            $this->config->getLogManager(),
            sprintf(FlagshipConstant::GET_MODIFICATION_INFO_ERROR, $key),
            [FlagshipConstant::PROCESS => FlagshipConstant::PROCESS_GET_MODIFICATION_INFO]
        );
        return null;
    }

    /**
     * Build the Campaign of Modification
     *
     * @param  Modification $modification Modification containing information
     * @return array JSON encoded string
     */
    private function parseToCampaign(Modification $modification)
    {
        return [
            FlagshipField::FIELD_CAMPAIGN_ID => $modification->getCampaignId(),
            FlagshipField::FIELD_VARIATION_GROUP_ID => $modification->getVariationGroupId(),
            FlagshipField::FIELD_VARIATION_ID => $modification->getVariationId(),
            FlagshipField::FIELD_IS_REFERENCE => $modification->getIsReference()
        ];
    }

    /**
     * This function will call the decision api and update all the campaigns modifications
     * from the server according to the visitor context.
     */
    public function synchronizedModifications()
    {
        $this->modifications = $this->decisionAPi->getCampaignsModifications($this, HttpClient::create());
    }
}
