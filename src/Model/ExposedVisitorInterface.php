<?php

namespace Flagship\Model;

interface ExposedVisitorInterface
{
    /**
     * Visitor id
     * @return string
     */
    public function getId();

    /**
     * visitor anonymous id
     * @return string
     */
    public function getAnonymousId();

    /**
     * visitor context
     * @return array
     */
    public function getContext();
}
