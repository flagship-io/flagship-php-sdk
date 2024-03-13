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
     * Return an array of all flags data fetched for the current visitor.
     * @return FlagDTO[]
     */
    public function getFlagsDTO();
}
