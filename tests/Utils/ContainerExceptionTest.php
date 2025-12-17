<?php

namespace Flagship\Utils;

use Flagship\BaseTestCase;

class ContainerExceptionTest extends BaseTestCase
{
    public function testUnresolvable(): void
    {
        $exception = ContainerException::unresolvable(\stdClass::class);
        $this->assertEquals('Unable to resolve class "stdClass"', $exception->getMessage());
    }

    public function testCircularDependency(): void
    {
        $chain = [\stdClass::class, \DateTime::class, \Exception::class];
        $exception = ContainerException::circularDependency($chain);
        $this->assertEquals(
            'Circular dependency detected: stdClass -> DateTime -> Exception',
            $exception->getMessage()
        );
    }

    public function testNotInstantiable(): void
    {
        $exception = ContainerException::notInstantiable(\stdClass::class);
        $this->assertEquals('Class "stdClass" is not instantiable', $exception->getMessage());
    }
}
