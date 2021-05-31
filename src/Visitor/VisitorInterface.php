<?php

namespace Flagship\Visitor;

use Flagship\Hit\HitAbstract;

/**
 * Flagship visitor representation.
 *
 * @package Flagship
 */
interface VisitorInterface
{
    /**
     * Update the visitor context values, matching the given keys, used for targeting.
     *
     * A new context value associated with this key will be created if there is no previous matching value.
     * Context key must be String, and value type must be one of the following : Number, Boolean, String.
     *
     * @param string $key : context key.
     * @param numeric|string|bool $value : context value.
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
     * Retrieve a modification value by its key. If no modification match the given
     * key or if the stored value type and default value type do not match, default value will be returned.
     *
     * @param string              $key          : key associated to the modification.
     * @param string|bool|numeric|array $defaultValue : default value to return.
     * @param bool                $activate     : Set this parameter to true to automatically
     *                                          report on our server that the
     *                                          current visitor has seen this modification. It is possible to call
     *                                          activateModification() later.

     * @return string|bool|numeric|array : modification value or default value.
     */
    public function getModification($key, $defaultValue, $activate = false);

    /**
     * @return array
     */
    public function getModifications();

    /**
     * Get the campaign modification information value matching the given key.
     *
     * @param string $key : key which identify the modification.
     * @return array|null
     */
    public function getModificationInfo($key);

    /**
     * This function calls the decision api and update all the campaigns modifications
     * from the server according to the visitor context.
     * @return void
     */
    public function synchronizedModifications();

    /**
     * Report this user has seen this modification.
     *
     * @param $key : key which identify the modification to activate.
     * @return void
     */
    public function activateModification($key);

    /**
     * Send a Hit to Flagship servers for reporting.
     * @param HitAbstract $hit
     * @return void
     */
    public function sendHit(HitAbstract $hit);
}
