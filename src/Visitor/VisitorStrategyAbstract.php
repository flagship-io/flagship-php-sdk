<?php

namespace Flagship\Visitor;

abstract class VisitorStrategyAbstract implements VisitorInterface
{
    /**
     * @var VisitorAbstract
     */
    protected $visitor;

    public function __construct(VisitorAbstract $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @return VisitorAbstract
     */
    protected function getVisitor()
    {
        return $this->visitor;
    }
}
