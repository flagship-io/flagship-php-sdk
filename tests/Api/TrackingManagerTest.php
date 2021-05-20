<?php

namespace Flagship\Api;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\FlagshipConfig;
use Flagship\Hit\Page;
use Flagship\Model\HttpResponse;
use Flagship\Model\Modification;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\HttpClient;
use Flagship\Visitor;
use PHPUnit\Framework\TestCase;

class TrackingManagerTest extends TestCase
{
    public function testConstruct()
    {
        $httpClient = new HttpClient();
        $trackingManager = new TrackingManager($httpClient);
        $this->assertSame($httpClient, $trackingManager->getHttpClient());
    }

    public function testSendActive()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $config = new FlagshipConfig('envId', 'apiKey');

        $configManager = new ConfigManager();
        $configManager->setConfig($config);

        $modification = new Modification();
        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');
        $visitor = new Visitor($configManager, 'visitorId', []);

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $httpClientMock->expects($this->once())->method('post')->with(
            $url,
            [],
            [
                FlagshipConstant::VISITOR_ID_API_ITEM => $visitor->getVisitorId(),
                FlagshipConstant::VARIATION_ID_API_ITEM => $modification->getVariationId(),
                FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $modification->getVariationGroupId(),
                FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId()
            ]
        )->willReturn(new HttpResponse(204, null));

        $trackingManager->sendActive($visitor, $modification);
    }

    public function testSendActiveThrowException()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $config = new FlagshipConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = new ConfigManager();
        $configManager->setConfig($config);

        $modification = new Modification();

        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $visitor = new Visitor($configManager, 'visitorId', []);

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $exception = new Exception();
        $httpClientMock->expects($this->once())->method('post')->with(
            $url,
            [],
            [
                FlagshipConstant::VISITOR_ID_API_ITEM => $visitor->getVisitorId(),
                FlagshipConstant::VARIATION_ID_API_ITEM => $modification->getVariationId(),
                FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $modification->getVariationGroupId(),
                FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId()
            ]
        )->willThrowException($exception);

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManagerStub->expects($this->once())
            ->method('error')
            ->with("[$flagshipSdk] " . $exception->getMessage());


        $trackingManager->sendActive($visitor, $modification);
    }

    public function testSendHit()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $modification = new Modification();
        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $pageUrl = "https://localhost";
        $page = new Page($pageUrl);
        $page->setConfig(new FlagshipConfig());

        $url = FlagshipConstant::HIT_API_URL;

        $httpClientMock->expects($this->once())->method('post')->with(
            $url,
            [],
            $page->toArray()
        );

        $trackingManager->sendHit($page);
    }

    public function testSendHitThrowException()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $config = new FlagshipConfig('envId', 'apiKey');

        $config->setLogManager($logManagerStub);

        $modification = new Modification();

        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $pageUrl = "Https://localhost";
        $page = new Page($pageUrl);
        $page->setConfig(new FlagshipConfig());

        $url = FlagshipConstant::HIT_API_URL;

        $exception = new Exception();
        $httpClientMock->expects($this->once())->method('post')->with(
            $url,
            [],
            $page->toArray()
        )->willThrowException($exception);

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManagerStub->expects($this->once())
            ->method('error')
            ->with("[$flagshipSdk] " . $exception->getMessage());

        $page->getConfig()->setLogManager($logManagerStub);

        $trackingManager->sendHit($page);
    }
}
