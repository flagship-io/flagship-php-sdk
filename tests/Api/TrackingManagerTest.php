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


    public function testConstruct()
    {
        $config = new DecisionApiConfig();
        $httpClient = new HttpClient();
        $trackingManager = new TrackingManager($config, $httpClient);
        $this->assertSame($httpClient, $trackingManager->getHttpClient());
        $this->assertSame($config, $trackingManager->getConfig());
    }
}
