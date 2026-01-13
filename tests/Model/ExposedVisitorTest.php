<?php

declare(strict_types=1);

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;

class ExposedVisitorTest extends TestCase
{
    public function testConstructor()
    {
        $context = ['key1' => 'value1', 'key2' => 123];
        $visitor = new ExposedVisitor('visitor123', 'anon456', $context);

        $this->assertSame('visitor123', $visitor->getId());
        $this->assertSame('anon456', $visitor->getAnonymousId());
        $this->assertSame($context, $visitor->getContext());
    }

    public function testConstructorWithNullAnonymousId()
    {
        $context = ['key1' => 'value1'];
        $visitor = new ExposedVisitor('visitor123', null, $context);

        $this->assertSame('visitor123', $visitor->getId());
        $this->assertNull($visitor->getAnonymousId());
        $this->assertSame($context, $visitor->getContext());
    }

    public function testConstructorWithEmptyContext()
    {
        $visitor = new ExposedVisitor('visitor123', 'anon456', []);

        $this->assertSame('visitor123', $visitor->getId());
        $this->assertSame('anon456', $visitor->getAnonymousId());
        $this->assertSame([], $visitor->getContext());
    }

    public function testGetId()
    {
        $visitor = new ExposedVisitor('user-id-123', null, []);
        $this->assertSame('user-id-123', $visitor->getId());
    }

    public function testGetAnonymousId()
    {
        $visitor = new ExposedVisitor('user-id', 'anonymous-id-789', []);
        $this->assertSame('anonymous-id-789', $visitor->getAnonymousId());
    }

    public function testGetContext()
    {
        $context = [
            'browser' => 'chrome',
            'version' => '120',
            'age' => 25,
            'premium' => true
        ];
        $visitor = new ExposedVisitor('user-id', null, $context);
        $this->assertSame($context, $visitor->getContext());
    }

    public function testImplementsInterface()
    {
        $visitor = new ExposedVisitor('id', null, []);
        $this->assertInstanceOf(ExposedVisitorInterface::class, $visitor);
    }
}
