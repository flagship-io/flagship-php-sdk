<?php

namespace Flagship\Model;

/**
 *
 */
class ExposedVisitor implements ExposedVisitorInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $anonymousId;

    /**
     * @var array
     */
    private $context;

    /**
     * @param string $id
     * @param string $anonymousId
     * @param array $context
     */
    public function __construct($id, $anonymousId, array $context)
    {
        $this->id = $id;
        $this->anonymousId = $anonymousId;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
