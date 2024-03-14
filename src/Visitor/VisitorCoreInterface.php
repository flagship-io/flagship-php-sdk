<?php

namespace Flagship\Visitor;

use Flagship\Hit\HitAbstract;

interface VisitorCoreInterface
{
    /**
     * Set if visitor has consented for private data usage.
     * @param bool $hasConsented True if the visitor has consented false otherwise.
     * @return void
     */
    public function setConsent($hasConsented);
    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context key must be String, and value type must be one of the following : Number, Boolean, String.
     *
     * @param string $key  context key.
     * @param numeric|string|bool $value : context value.
     * @return void
     */
    public function updateContext($key, $value);

    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context keys must be String, and values types must be one of the following : Number, Boolean, String.
     *
     * @param array $context : collection of keys, values. e.g: ["age"=>42, "IsVip"=>true, "country"=>"UK"]
     */
    public function updateContextCollection(array $context);

    /**
     * clear the actual visitor context
     * @return void
     */
    public function clearContext();

    /**
     * In DecisionApi Mode this function calls the Flagship Decision API to run
     * campaign assignments according to the current user context
     * and retrieve applicable flags. <br/>
     * In bucketing Mode, it checks bucketing file,
     * validates campaigns targeting the visitor,
     * assigns a variation and retrieve applicable flags
     * @return void
     */
    public function fetchFlags();

    /**
     * Send a Hit to Flagship servers for reporting.
     * @param HitAbstract $hit
     * @return void
     */
    public function sendHit(HitAbstract $hit);

      /**
     * Authenticate anonymous visitor
     * @param string $visitorId
     * @return void
     */
    public function authenticate($visitorId);

    /**
     * This function change authenticated Visitor to anonymous visitor
     * @return void
     */
    public function unauthenticate();
}
