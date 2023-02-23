<?php

namespace Flagship\Model;


/**
 *
 */
interface ExposedUserInterface
{
    /**
     * @return string
     */
    public function getVisitorId();

    /**
     * @return string
     */
    public function getAnonymousId();

    /**
     * @return array
     */
    public function getContext();
}