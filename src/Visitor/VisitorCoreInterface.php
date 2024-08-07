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
    public function setConsent(bool $hasConsented): void;
    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context key must be String, and value type must be one of the following : Number, Boolean, String.
     *
     * @param string $key  context key.
     * @param bool|string|numeric $value : context value.
     * @return void
     */
    public function updateContext(string $key, float|bool|int|string|null $value): void;

    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context keys must be String, and values types must be one of the following : Number, Boolean, String.
     *
     * @param array $context collection of keys, values. e.g: ["age"=>42, "IsVip"=>true, "country"=>"UK"]
     */
    public function updateContextCollection(array $context);

    /**
     * Clear the visitor's context
     * @return void
     */
    public function clearContext(): void;

    /**
     * In DecisionApi Mode this function calls the Flagship Decision API to run
     * campaign assignments according to the current user context
     * and retrieve applicable flags. <br/>
     * In bucketing Mode, it checks bucketing file,
     * validates campaigns targeting the visitor,
     * assigns a variation and retrieve applicable flags
     * @return void
     */
    public function fetchFlags(): void;

    /**
     * Send a Hit to Flagship servers for reporting.
     * @param HitAbstract $hit
     * @return void
     */
    public function sendHit(HitAbstract $hit): void;

      /**
     * Authenticate anonymous visitor
     * @param string $visitorId
     * @return void
     */
    public function authenticate(string $visitorId): void;

    /**
     * This function change authenticated Visitor to anonymous visitor
     * @return void
     */
    public function unauthenticate(): void;
}
