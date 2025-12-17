<?php

namespace Flagship\Utils;

use PHPUnit\Framework\TestCase;
use stdClass;

// Test helper classes
interface TestInterface {}

class ConcreteTestClass implements TestInterface
{
    public function __construct(public string $value = 'default') {}
}

class SimpleClass {
    public ?string $custom = null;
}

class ClassWithDependency
{
    public function __construct(public SimpleClass $dependency) {}
}

class ClassWithMultipleDependencies
{
    public function __construct(
        public SimpleClass $dep1,
        public ConcreteTestClass $dep2
    ) {}
}

class ClassWithDefaultValue
{
    public function __construct(public string $name = 'test', public int $value = 42) {}
}

class ClassWithMixedParameters
{
    public function __construct(
        public string $required,
        public SimpleClass $dependency,
        public string $optional = 'default'
    ) {}
}

class ClassWithNullableParameter
{
    public function __construct(public ?SimpleClass $dependency) {}
}

class ClassWithNullableAndDefaultParameter
{
    public function __construct(public ?SimpleClass $dependency = null) {}
}

class ClassWithBuiltInTypes
{
    public function __construct(
        public string $str,
        public int $num ,
        public bool $flag,
        public float $decimal,
        public array $arr
    ) {}
}

abstract class AbstractTestClass {}

class ClassWithUnionType
{
    public function __construct(public SimpleClass|ConcreteTestClass|null $dependency, public int|bool $value, public ?bool $value2) {}
}

class ClassWithNoTypeHint
{
    public function __construct(public $untyped) {}
}

class ClassWithUntypedButDefault
{
    public function __construct(public $value = 'default') {}
}

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    // Test bind() method
    public function testBindCreatesBinding(): void
    {
        $this->container->bind(TestInterface::class, ConcreteTestClass::class);

        $bindings = $this->container->getBindings();
        $this->assertArrayHasKey(TestInterface::class, $bindings);
        $this->assertEquals(ConcreteTestClass::class, $bindings[TestInterface::class]);
    }

    public function testBindReturnsSelf(): void
    {
        $result = $this->container->bind(TestInterface::class, ConcreteTestClass::class);
        $this->assertSame($this->container, $result);
    }

    public function testBindThrowsExceptionForDuplicateAlias(): void
    {
        $this->container->bind(TestInterface::class, ConcreteTestClass::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Alias "Flagship\Utils\TestInterface" is already bound');

        $this->container->bind(TestInterface::class, SimpleClass::class);
    }

    public function testBindThrowsExceptionForNonExistentClass(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Class "NonExistentClass" does not exist');

        $this->container->bind(TestInterface::class, 'NonExistentClass');
    }

    // Test factory() method
    public function testFactoryRegistersCallback(): void
    {
        $this->container->factory(SimpleClass::class, function ($container) {
            return new SimpleClass();
        });

        $instance = $this->container->get(SimpleClass::class);
        $this->assertInstanceOf(SimpleClass::class, $instance);
    }

    public function testFactoryReturnsSelf(): void
    {
        $result = $this->container->factory(SimpleClass::class, fn() => new SimpleClass());
        $this->assertSame($this->container, $result);
    }

    public function testFactoryReceivesContainerAndArgs(): void
    {
        $this->container->factory(ConcreteTestClass::class, function ($container, $args) {
            $this->assertInstanceOf(Container::class, $container);
            $value = $args[0] ?? 'factory';
            return new ConcreteTestClass($value);
        });

        $instance = $this->container->get(ConcreteTestClass::class, ['custom']);
        $this->assertEquals('custom', $instance->value);
    }

    public function testFactoryThrowsExceptionWhenNotReturningObject(): void
    {
        $this->container->factory(SimpleClass::class, fn() => 'not an object');

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Factory for "Flagship\Utils\SimpleClass" must return an object');

        $this->container->get(SimpleClass::class);
    }

    // Test instance() method
    public function testInstanceRegistersSingleton(): void
    {
        $instance = new SimpleClass();
        $this->container->instance(SimpleClass::class, $instance);

        $retrieved = $this->container->get(SimpleClass::class);
        $this->assertSame($instance, $retrieved);
    }

    public function testInstanceReturnsSelf(): void
    {
        $result = $this->container->instance(SimpleClass::class, new SimpleClass());
        $this->assertSame($this->container, $result);
    }

    // Test has() method
    public function testHasReturnsTrueForRegisteredInstance(): void
    {
        $this->container->instance(SimpleClass::class, new SimpleClass());
        $this->assertTrue($this->container->has(SimpleClass::class));
    }

    public function testHasReturnsTrueForBinding(): void
    {
        $this->container->bind(TestInterface::class, ConcreteTestClass::class);
        $this->assertTrue($this->container->has(TestInterface::class));
    }

    public function testHasReturnsTrueForFactory(): void
    {
        $this->container->factory(SimpleClass::class, fn() => new SimpleClass());
        $this->assertTrue($this->container->has(SimpleClass::class));
    }

    public function testHasReturnsTrueForExistingClass(): void
    {
        $this->assertTrue($this->container->has(SimpleClass::class));
    }

    public function testHasReturnsFalseForNonExistent(): void
    {
        $this->assertFalse($this->container->has('NonExistentClass'));
    }

    // Test get() method
    public function testGetCreatesSimpleInstance(): void
    {
        $instance = $this->container->get(SimpleClass::class);
        $this->assertInstanceOf(SimpleClass::class, $instance);
    }

    public function testGetReturnsSameInstanceForSingleton(): void
    {
        $first = $this->container->get(SimpleClass::class);
        $second = $this->container->get(SimpleClass::class);
        $this->assertSame($first, $second);
    }

    public function testGetWithFactoryModeCreatesNewInstance(): void
    {
        $first = $this->container->get(SimpleClass::class, null, true);
        $second = $this->container->get(SimpleClass::class, null, true);
        $this->assertNotSame($first, $second);
    }

    public function testGetResolvesBinding(): void
    {
        $this->container->bind(TestInterface::class, ConcreteTestClass::class);
        $instance = $this->container->get(TestInterface::class);
        $this->assertInstanceOf(ConcreteTestClass::class, $instance);
    }

    public function testGetWithConstructorArgs(): void
    {
        $instance = $this->container->get(ConcreteTestClass::class, ['custom_value']);
        $this->assertEquals('custom_value', $instance->value);
    }

    public function testGetAutoResolvesDependencies(): void
    {
        $instance = $this->container->get(ClassWithDependency::class);
        $this->assertInstanceOf(ClassWithDependency::class, $instance);
        $this->assertInstanceOf(SimpleClass::class, $instance->dependency);
    }

    public function testGetResolvesMultipleDependencies(): void
    {
        $instance = $this->container->get(ClassWithMultipleDependencies::class);
        $this->assertInstanceOf(SimpleClass::class, $instance->dep1);
        $this->assertInstanceOf(ConcreteTestClass::class, $instance->dep2);
    }

    public function testGetUsesDefaultValues(): void
    {
        $instance = $this->container->get(ClassWithDefaultValue::class);
        $this->assertEquals('test', $instance->name);
        $this->assertEquals(42, $instance->value);
    }

    public function testGetHandlesNullableParameters(): void
    {
        $instance = $this->container->get(ClassWithNullableParameter::class);
        $this->assertInstanceOf(SimpleClass::class, $instance->dependency);
    }

    public function testGetHandlesNullableWithDefaultParameters(): void
    {
        $instance = $this->container->get(ClassWithNullableAndDefaultParameter::class);
        $this->assertNull($instance->dependency);
    }

    public function testGetResolvesBuiltInTypes(): void
    {
        $instance = $this->container->get(ClassWithBuiltInTypes::class);
        $this->assertSame('', $instance->str);
        $this->assertSame(0, $instance->num);
        $this->assertFalse($instance->flag);
        $this->assertSame(0.0, $instance->decimal);
        $this->assertSame([], $instance->arr);
    }

    public function testGetThrowsExceptionForAbstractClass(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('is not instantiable');

        $this->container->get(AbstractTestClass::class);
    }

    public function testGetThrowsExceptionForNonExistentClass(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Class "NonExistentClass" does not exist');

        $this->container->get('NonExistentClass');
    }

    public function testGetResolvesUnionType(): void
    {
        $instance = $this->container->get(ClassWithUnionType::class);
        $this->assertInstanceOf(ClassWithUnionType::class, $instance);
        $this->assertInstanceOf(SimpleClass::class, $instance->dependency);
        $this->assertIsInt($instance->value);
        $this->assertNull($instance->value2);
    }

    public function testGetForUntypedParameterWithoutDefault(): void
    {
        $instance = $this->container->get(ClassWithNoTypeHint::class);
        $this->assertInstanceOf(ClassWithNoTypeHint::class, $instance);
    }

    public function testGetHandlesUntypedParameterWithDefault(): void
    {

        $instance = $this->container->get(ClassWithUntypedButDefault::class);

        $this->assertEquals('default', $instance->value);
    }

    // Test make() method
    public function testMakeCreatesNewInstance(): void
    {
        $first = $this->container->make(SimpleClass::class);
        $second = $this->container->make(SimpleClass::class);
        $this->assertNotSame($first, $second);
    }

    public function testMakeWithArgs(): void
    {
        $instance = $this->container->make(ConcreteTestClass::class, ['made']);
        $this->assertEquals('made', $instance->value);
    }

    // Test flush() method
    public function testFlushClearsAllInstances(): void
    {
        $first = $this->container->get(SimpleClass::class);
        $this->container->flush();
        $second = $this->container->get(SimpleClass::class);

        $this->assertNotSame($first, $second);
    }

    public function testFlushReturnsSelf(): void
    {
        $result = $this->container->flush();
        $this->assertSame($this->container, $result);
    }

    // Test forget() method
    public function testForgetClearsSpecificInstance(): void
    {
        $first = $this->container->get(SimpleClass::class);
        $this->container->forget(SimpleClass::class);
        $second = $this->container->get(SimpleClass::class);

        $this->assertNotSame($first, $second);
    }

    public function testForgetReturnsSelf(): void
    {
        $this->container->get(SimpleClass::class);
        $result = $this->container->forget(SimpleClass::class);
        $this->assertSame($this->container, $result);
    }

    public function testForgetDoesNotAffectOtherInstances(): void
    {
        $simple = $this->container->get(SimpleClass::class);
        $concrete = $this->container->get(ConcreteTestClass::class);

        $this->container->forget(SimpleClass::class);

        $newSimple = $this->container->get(SimpleClass::class);
        $sameConcrete = $this->container->get(ConcreteTestClass::class);

        $this->assertNotSame($simple, $newSimple);
        $this->assertSame($concrete, $sameConcrete);
    }

    // Test getBindings() method
    public function testGetBindingsReturnsEmptyArrayInitially(): void
    {
        $bindings = $this->container->getBindings();
        $this->assertIsArray($bindings);
        $this->assertEmpty($bindings);
    }

    public function testGetBindingsReturnsAllBindings(): void
    {
        $this->container->bind(TestInterface::class, ConcreteTestClass::class);
        $this->container->bind('AnotherAlias', SimpleClass::class);

        $bindings = $this->container->getBindings();
        $this->assertCount(2, $bindings);
        $this->assertEquals(ConcreteTestClass::class, $bindings[TestInterface::class]);
        $this->assertEquals(SimpleClass::class, $bindings['AnotherAlias']);
    }

    // Integration tests
    public function testChainedMethodCalls(): void
    {
        $instance = new SimpleClass();

        $this->container
            ->bind(TestInterface::class, ConcreteTestClass::class)
            ->factory('test', fn() => new stdClass())
            ->instance(SimpleClass::class, $instance);

        $this->assertSame($instance, $this->container->get(SimpleClass::class));
        $this->assertInstanceOf(ConcreteTestClass::class, $this->container->get(TestInterface::class));
    }

    public function testComplexDependencyResolution(): void
    {
        $this->container->bind(TestInterface::class, ConcreteTestClass::class);

        $instance = $this->container->get(ClassWithMultipleDependencies::class);

        $this->assertInstanceOf(SimpleClass::class, $instance->dep1);
        $this->assertInstanceOf(ConcreteTestClass::class, $instance->dep2);
    }

    public function testFactoryTakesPrecedenceOverAutoResolution(): void
    {
        $this->container->factory(SimpleClass::class, function () {
            $instance = new SimpleClass();
            $instance->custom = 'factory-created';
            return $instance;
        });

        $instance = $this->container->get(SimpleClass::class);
        $this->assertTrue(property_exists($instance, 'custom'));
        $this->assertEquals('factory-created', $instance->custom);
    }
}
