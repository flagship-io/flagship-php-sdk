<?php

namespace Flagship\Model;

/**
 *
 */
class ExposedUser implements ExposedUserInterface
{
    /**
     * @var string
     */
    private $visitorId;

    /**
     * @var string
     */
    private $anonymousId;

    /**
     * @var array
     */
    private $context;

    /**
     * @param string $visitorId
     * @param string $anonymousId
     * @param array $context
     */
    public function __construct($visitorId, $anonymousId, array $context)
    {
        $this->visitorId = $visitorId;
        $this->anonymousId = $anonymousId;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitorId;
    }

    /**
     * @return string
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}