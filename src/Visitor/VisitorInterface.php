<?php

namespace Flagship\Visitor;

use Flagship\Flag\FlagInterface;
use Flagship\Model\FlagDTO;

/**
 * Flagship visitor representation.
 *
 * @package Flagship
 */
interface VisitorInterface extends VisitorCoreInterface
{

    /**
     * Visitor unique identifier
     *
     * @return string
     */
    public function getVisitorId();

    /**
     * Visitor anonymous id
     *
     * @return string
     */
    public function getAnonymousId();

    /**
     * Set if visitor has consented for private data usage.
     * @param bool $hasConsented True if the visitor has consented false otherwise.
     * @return void
     */
    public function setConsent($hasConsented);

    /**
     * Get the current context
     *
     * @return array
     */
    public function getContext();


    /**
     * Retrieve a modification value by its key. If no modification match the given
     * key or if the stored value type and default value type do not match, default value will be returned.
     *
     * @param string              $key          key associated to the modification.
     * @param string|bool|numeric|array $defaultValue : default value to return.
     * @param bool                $activate     Set this parameter to true to automatically
     *                                          report on our server that the
     *                                          current visitor has seen this modification. It is possible to call
     *                                          activateModification() later.

     * @return string|bool|numeric|array : modification value or default value.
     * @deprecated use getFlag instead
     */
    public function getModification($key, $defaultValue, $activate = false);

    /**
     * @param string $key key associated to the flag
     * @param string|bool|numeric|array $defaultValue flag default value.
     * @return FlagInterface
     */
    public function getFlag($key, $defaultValue);

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

    /**
     * @deprecated
     * @return array
     */
    public function getModifications();

    /**
     * Return an array of all flags data fetched for the current visitor.
     * @return FlagDTO[]
     */
    public function getFlagsDTO();

    /**
     * Get the campaign modification information value matching the given key.
     * @deprecated
     * @param string $key key which identify the modification.
     * @return array|null
     */
    public function getModificationInfo($key);

    /**
     * @deprecated
     * In DecisionApi Mode this function calls the Flagship Decision API to run
     * campaign assignments according to the current user context
     * and retrieve applicable modifications. <br/>
     * In bucketing Mode, it checks bucketing file,
     * validates campaigns targeting the visitor,
     * assigns a variation and retrieve applicable modifications
     * @return void
     */
    public function synchronizeModifications();

    /**
     * Report this user has seen this modification.
     *
     * @deprecated
     * @param string $key key which identify the modification to activate.
     * @return void
     */
    public function activateModification($key);
}
