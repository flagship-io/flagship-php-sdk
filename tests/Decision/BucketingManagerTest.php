<?php

namespace Flagship\Decision;

use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\MurmurHash;
use Flagship\Visitor\VisitorDelegate;
use PHPUnit\Framework\TestCase;

class BucketingManagerTest extends TestCase
{
    public function testGetCampaignModification()
    {

        $murmurhash = new MurmurHash();
        $bucketingManager = new BucketingManager(new HttpClient(), $murmurhash);
        $visitorId = "visitor_1";
        $visitorContext = [
            "isPHP" => true
        ];
        $container = new Container();
        $configManager = new ConfigManager();
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, $visitorContext);

        $campaigns =  $bucketingManager->getCampaignModifications($visitor);

//        print_r($campaigns);
    }
}
