<?php

namespace Flagship;

use Flagship\Interfaces\ApiManagerInterface;
use Flagship\Decision\ApiManager;
use Flagship\utils\HttpClient;

/**
 * Flagship visitor representation.
 * @package Flagship
 */
class Visitor
{
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
     * @var array
     */
    private $modifications;

    /**
     * @var ApiManagerInterface
     */
    private $decisionAPi;

    /**
     * @var array|mixed
     */
    private $campaigns;

    /**
     * Create a new visitor.
     * @param FlagshipConfig $config : configuration used when the visitor has been created.
     * @param string $visitorId : visitor unique identifier.
     * @param array $context : visitor context. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
     */
    public function __construct($config, $visitorId, $context)
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
        if (!is_null($visitorId)) {
            $this->visitorId = $visitorId;
        } else {
            $this->log(); //Log visitorId empty
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
        if (!$this->isContextKeyValid($key) || !$this->isContextValueValid($value)) {
            $this->log();
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
     * This function will call the decision api and update all the campaigns modifications
     * from the server according to the visitor context.
     */
    public function synchronizedModications()
    {
        $this->campaigns = $this->decisionAPi->getCampaigns($this, HttpClient::create());
    }

    /**
     * Return true if a context key is not null and is a string, otherwise return false
     * @param mixed $key Context key
     * @return bool
     */
    private function isContextKeyValid($key)
    {
        return !is_null($key) && is_string($key);
    }


    /**
     * Return true if a context value is not null and is a number or a boolean or a string,
     * otherwise return false
     * @param $value
     * @return bool
     */
    private function isContextValueValid($value)
    {
        if (!is_null($value) && (is_numeric($value) || is_bool($value) || is_string($value))) {
            return true;
        }
        return false;
    }

    private function log($message = "Visitor", $context = null)
    {
        $logManger = $this->config->getLogManager();
        if (!is_null($logManger)) {
            $logManger->error($message, $context);
        }
    }
}
