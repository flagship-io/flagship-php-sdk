<?php

namespace Flagship\Cache;

use Flagship\Config\DecisionApiConfig;
use Flagship\Utils\ConfigManager;
use Flagship\Visitor\DefaultStrategy;
use Flagship\Visitor\VisitorDelegate;
use PHPUnit\Framework\TestCase;

class VisitorCache extends TestCase
{
    public function testLookupDefaultValue(){
        $configData = ['envId' => 'env_value', 'apiKey' => 'key_value'];
        $config = new DecisionApiConfig($configData['envId'], $configData['apiKey']);
        $visitorId = "visitor_id";

        $visitorContext = [
            'name' => 'visitor_name',
            'age' => 25
        ];

        $configManager = (new ConfigManager())->setConfig($config);

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $defaultStrategy = $this->getMockBuilder('Flagship\Visitor\DefaultStrategy')
            ->setMethods(['lookupVisitor'])->disableOriginalConstructor()
            ->getMock();

        $containerMock->method('get')->willReturn($defaultStrategy);

        $defaultStrategy->expects($this->once())->method("lookupVisitor");

        $visitor = new VisitorDelegate($containerMock, $configManager, $visitorId, false, $visitorContext, true);

        $defaultStrategy = new DefaultStrategy($visitor);

        $defaultStrategy->lookupVisitor();

    }
}