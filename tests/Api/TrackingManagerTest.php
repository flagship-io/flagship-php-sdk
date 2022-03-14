<?php

namespace Flagship\Api;
require_once __dir__ . "/../Assets/ShellExec.php";

use Exception;
use Flagship\Assets\ShellExec;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use Flagship\Hit\Page;
use Flagship\Model\HttpResponse;
use Flagship\Model\FlagDTO;
use Flagship\Traits\BuildApiTrait;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Visitor;
use PHPUnit\Framework\TestCase;

class TrackingManagerTest extends TestCase
{
    use BuildApiTrait;
    const PSR_LOG_INTERFACE = 'Psr\Log\LoggerInterface';

    protected function buildBackRequest($url, $body, $headers, $timeout, $logFile)
    {
        $bodyArg = escapeshellarg(json_encode($body));
        $headersArg = escapeshellarg(json_encode($headers));
        $timeoutArg = $timeout;
        $args = " --url=$url";
        $args .= " --body=$bodyArg";
        $args .= " --header=$headersArg";
        $args .= " --timeout=$timeoutArg";



        $reflector = new \ReflectionClass("Flagship\Api\TrackingManager");
        $directory = dirname($reflector->getFileName());
        return "nohup php " . $directory . "/backgroundRequest.php $args >>" . $directory . "/$logFile 2>&1  &";
    }

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

        $config = new DecisionApiConfig('envId', 'apiKey');

        $configManager = new ConfigManager();
        $configManager->setConfig($config)->setTrackingManager($trackingManager);

        $modification = new FlagDTO();
        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');
        $visitor = new Visitor\VisitorDelegate(new Container(), $configManager, 'visitorId', false, []);

        $url = FlagshipConstant::BASE_API_URL . '/' . FlagshipConstant::URL_ACTIVATE_MODIFICATION;

        $authenticatedId = "authenticatedId";

        $httpClientMock->expects($this->exactly(2))->method('post')->withConsecutive(
            [$url,
                [],
                [
                    FlagshipConstant::VISITOR_ID_API_ITEM => $visitor->getVisitorId(),
                    FlagshipConstant::VARIATION_ID_API_ITEM => $modification->getVariationId(),
                    FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $modification->getVariationGroupId(),
                    FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
                    FlagshipConstant::ANONYMOUS_ID => null
                ]],
            [$url,
                [],
                [
                    FlagshipConstant::VISITOR_ID_API_ITEM => $authenticatedId,
                    FlagshipConstant::VARIATION_ID_API_ITEM => $modification->getVariationId(),
                    FlagshipConstant::VARIATION_GROUP_ID_API_ITEM => $modification->getVariationGroupId(),
                    FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
                    FlagshipConstant::ANONYMOUS_ID => $visitor->getVisitorId()
                ]]
        )->willReturn(new HttpResponse(204, null));

        $trackingManager->sendActive($visitor, $modification);

        $visitor->authenticate($authenticatedId);

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
            self::PSR_LOG_INTERFACE,
            ['error'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = new ConfigManager();
        $configManager->setConfig($config)->setTrackingManager($trackingManager);

        $modification = new FlagDTO();

        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $visitor = new Visitor\VisitorDelegate(new Container(), $configManager, 'visitorId', false, []);

        $exception = new Exception();
        $httpClientMock->expects($this->once())->method('post')->willThrowException($exception);

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

        $config = new DecisionApiConfig('envId', 'apiKey');

        $trackingManager = new TrackingManager($httpClientMock);


        $modification = new FlagDTO();
        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $pageUrl = "https://localhost";
        $page = new Page($pageUrl);
        $page->setConfig(new DecisionApiConfig());

        $url = FlagshipConstant::HIT_API_URL;

        $page->setConfig($config);
        $trackingManager->sendHit($page);

        $command = $this->buildBackRequest($url, $page->toArray(), $this->buildHeader($config->getApiKey()), $config->getTimeout()/1000, TrackingManager::HIT_LOG);
        $this->assertEquals($command, ShellExec::$command);
        ShellExec::$command = null;
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
            self::PSR_LOG_INTERFACE,
            ['error'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $config = new DecisionApiConfig('envId', 'apiKey');

        $config->setLogManager($logManagerStub);

        $modification = new FlagDTO();

        $modification
            ->setKey('background')
            ->setValue('EE3300')
            ->setIsReference(false)
            ->setVariationGroupId('c1e3t1nvfu1ncqfcdcp0')
            ->setCampaignId('c1e3t1nvfu1ncqfcdco0')
            ->setVariationId('c1e3t1nvfu1ncqfcdcq0');

        $pageUrl = "Https://localhost";
        $page = new Page($pageUrl);
        $page->setConfig(new DecisionApiConfig());

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;

        $exceptionMessage = "message error";

        $logManagerStub->expects($this->once())
            ->method('error')
            ->with("[$flagshipSdk] " . $exceptionMessage);

        $page->getConfig()->setLogManager($logManagerStub);

        ShellExec::$toThrowException = $exceptionMessage;
        $trackingManager->sendHit($page);

        ShellExec::$toThrowException = null;
    }

    public function testSendConsentHit()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $config = new DecisionApiConfig('envId', 'apiKey');

        $configManager = new ConfigManager();
        $configManager->setConfig($config)->setTrackingManager($trackingManager);

        $authenticatedId = "authenticatedId";
        $visitorId = 'visitorId';

        $visitor = new Visitor\VisitorDelegate(new Container(), $configManager, $visitorId, false, []);

        $url = FlagshipConstant::HIT_CONSENT_URL;

        $body = [
            FlagshipConstant::T_API_ITEM => HitType::EVENT,
            FlagshipConstant::EVENT_LABEL_API_ITEM =>
                FlagshipConstant::SDK_LANGUAGE . ":" . ($visitor->hasConsented() ? "true" : "false"),
            FlagshipConstant::EVENT_ACTION_API_ITEM => "fs_content",
            FlagshipConstant::EVENT_CATEGORY_API_ITEM => EventCategory::USER_ENGAGEMENT,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::CUSTOMER_UID => null
        ];

        $command = $this->buildBackRequest($url, $body, $this->buildHeader($config->getApiKey()), $config->getTimeout()/1000, TrackingManager::HIT_LOG);
        $trackingManager->sendConsentHit($visitor, $config);

        $this->assertEquals($command, ShellExec::$command);

        $visitor->authenticate($authenticatedId);

        $trackingManager->sendConsentHit($visitor, $config);

        $body = [
            FlagshipConstant::T_API_ITEM => HitType::EVENT,
            FlagshipConstant::EVENT_LABEL_API_ITEM =>
                FlagshipConstant::SDK_LANGUAGE . ":" . ($visitor->hasConsented() ? "true" : "false"),
            FlagshipConstant::EVENT_ACTION_API_ITEM => "fs_content",
            FlagshipConstant::EVENT_CATEGORY_API_ITEM => EventCategory::USER_ENGAGEMENT,
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $config->getEnvId(),
            FlagshipConstant::DS_API_ITEM => FlagshipConstant::SDK_APP,
            FlagshipConstant::VISITOR_ID_API_ITEM => $visitorId,
            FlagshipConstant::CUSTOMER_UID => $authenticatedId
        ];

        $command = $this->buildBackRequest($url, $body, $this->buildHeader($config->getApiKey()), $config->getTimeout()/1000, TrackingManager::HIT_LOG);
        $this->assertEquals($command, ShellExec::$command);
    }

    public function testSendConsentHitThrowException()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            '',
            false
        );

        $logManagerStub = $this->getMockForAbstractClass(
            self::PSR_LOG_INTERFACE,
            ['error'],
            '',
            false
        );

        $trackingManager = new TrackingManager($httpClientMock);

        $config = new DecisionApiConfig('envId', 'apiKey');
        $config->setLogManager($logManagerStub);

        $configManager = new ConfigManager();
        $configManager->setConfig($config)->setTrackingManager($trackingManager);

        $visitor = new Visitor\VisitorDelegate(new Container(), $configManager, 'visitorId', false, []);

        $flagshipSdk = FlagshipConstant::FLAGSHIP_SDK;
        $exceptionMessage = "message error";

        $logManagerStub->expects($this->once())
            ->method('error')
            ->with("[$flagshipSdk] " . $exceptionMessage);

        ShellExec::$toThrowException = $exceptionMessage;
        $trackingManager->sendConsentHit($visitor, $config);

        ShellExec::$toThrowException = null;
    }
}
