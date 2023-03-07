<?php

namespace Flagship\Utils\Version8;

use Flagship\Utils\Container;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testGet()
    {
        $container = new Container();
        $alias = 'Flagship\Utils\HttpClientInterface';
        $className = 'Flagship\Utils\HttpClient';

        $container->bind($alias, $className);

        //Test constructor without argument
        $instanceAlias1 = $container->get($alias);
        $this->assertInstanceOf($alias, $instanceAlias1);

        //Test constructor with default argument
        $container->bind(
            "Flagship\Config\FlagshipConfig",
            "Flagship\Config\DecisionApiConfig"
        );
        $alias = 'Flagship\Decision\DecisionManagerAbstract';
        $className = 'Flagship\Decision\ApiManager';
        $container->bind($alias, $className);

        $instanceAlias = $container->get($alias);
        $this->assertInstanceOf($alias, $instanceAlias);

        //Test without constructor
        $instanceAlias = $container->get('stdClass');
        $this->assertInstanceOf('stdClass', $instanceAlias);

        $this->expectException('ReflectionException');

        $container->get('NotExist');
    }

    public function testGetWithDefaultArgs()
    {
        $className = 'Flagship\Config\DecisionApiConfig';
        $container = new Container();
        $instanceAlias = $container->get($className);
        $this->assertInstanceOf($className, $instanceAlias);
        $this->assertNull($instanceAlias->getEnvId());
        $this->assertNull($instanceAlias->getApiKey());
    }

    public function testGetNotInstantiable()
    {
        $container = new Container();
        $alias = 'Flagship\Decision\DecisionManagerAbstract';

        $this->expectException('Exception');

        $container->get($alias);
    }

    public function testGetWithCustomArgument()
    {
        //Test constructor with custom argument
        $container = new Container();
        $className = 'Flagship\Config\DecisionApiConfig';
        $envId = 'envId';
        $apiKey = 'apiKey';
        $instanceAlias = $container->get($className, [$envId, $apiKey]);
        $this->assertInstanceOf($className, $instanceAlias);
        $this->assertSame($envId, $instanceAlias->getEnvId());
        $this->assertSame($apiKey, $instanceAlias->getApiKey());
    }

    public function testFactory()
    {
        $className = 'Flagship\Config\DecisionApiConfig';
        $container = new Container();
        $envId = 'envId';
        $apiKey = 'apiKey';
        $instanceAlias = $container->get($className, [$envId, $apiKey], true);
        $this->assertInstanceOf($className, $instanceAlias);
        $this->assertSame($envId, $instanceAlias->getEnvId());
        $this->assertSame($apiKey, $instanceAlias->getApiKey());
    }

    public function testBind()
    {
        $container = new Container();
        $alias1 = 'Flagship\Utils\HttpClientInterface';
        $className1 = 'Flagship\Utils\HttpClient';

        $container->bind($alias1, $className1);
        $binding = Utils::getProperty($container, 'bindings')->getValue($container);
        $this->assertCount(1, $binding);
        $this->assertSame($binding[$alias1], $className1);

        $alias2 = 'Flagship\Decision\DecisionManagerAbstract';
        $className2 = 'Flagship\Utils\ApiManager';

        $container->bind($alias2, $className2);

        $binding = Utils::getProperty($container, 'bindings')->getValue($container);
        $this->assertCount(2, $binding);
        $this->assertSame($binding[$alias2], $className2);

        $this->expectException('Exception');

        $container->bind($alias2, $className2);
    }
}
