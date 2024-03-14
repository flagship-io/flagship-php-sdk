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
     * Return True if the visitor has consented for private data usage, otherwise return False.
     *
     * @return boolean
     */
    public function hasConsented();



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
     * Return an array of all flags data fetched for the current visitor.
     * @return FlagDTO[]
     */
    public function getFlagsDTO();
}
