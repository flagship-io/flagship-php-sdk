<?php

namespace Flagship\Decision;

require_once __dir__ . "/../Assets/File.php";

use Exception;
use Flagship\Assets\File;
use Flagship\Config\BucketingConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\FlagshipField;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\MurmurHash;
use Flagship\Utils\Utils;
use Flagship\Visitor\VisitorDelegate;
use PHPUnit\Framework\TestCase;

class BucketingManagerTest extends TestCase
{
    public function testGetCampaignModification()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            "",
            false
        );

        $httpClientMock->expects($this->exactly(6))
            ->method('post')
            ->willReturn(new HttpResponse(204, null));

        $murmurhash = new MurmurHash();
        $config = new BucketingConfig();
        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);
        $visitorId = "visitor_1";
        $visitorContext = [
            "age" => 20
        ];
        $container = new Container();
        $configManager = new ConfigManager();
        $configManager->setConfig($config);

        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        //Test File not exist
        $campaigns = $bucketingManager->getCampaignModifications($visitor);

        $this->assertCount(0, $campaigns);

        //Test file_get_contents failed
        File::$fileExist = true;
        File::$fileContent = false;
        $campaigns = $bucketingManager->getCampaignModifications($visitor);
        $this->assertCount(0, $campaigns);

        //Test Panic Mode
        File::$fileExist = true;
        File::$fileContent = '{"panic": true}';
        $campaigns = $bucketingManager->getCampaignModifications($visitor);

        $this->assertCount(0, $campaigns);

        //Test campaign property
        File::$fileExist = true;
        File::$fileContent = '{}';
        $campaigns = $bucketingManager->getCampaignModifications($visitor);

        $this->assertCount(0, $campaigns);

        //Test campaign[FIELD_VARIATION_GROUPS]
        File::$fileExist = true;
        File::$fileContent = '{"campaigns":[{}]}';

        $campaigns = $bucketingManager->getCampaignModifications($visitor);

        $this->assertCount(0, $campaigns);

        //

        File::$fileExist = true;
        File::$fileContent = \file_get_contents(__DIR__ . '/bucketing.json');

        $campaigns = $bucketingManager->getCampaignModifications($visitor);

        $this->assertCount(6, $campaigns);
    }

    public function testSendContext()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error'],
            '',
            false
        );

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            "",
            false
        );

        $envId = "envId";

        $visitorId = "visitor_1";
        $visitorContext = [
            "age" => 20,
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
        ];

        $postBody = [
            "visitorId" => $visitorId,
            "type" => "CONTEXT",
            "data" => $visitorContext
        ];

        $errorMessage = "message error";
        $httpClientMock->expects($this->exactly(2))
            ->method('post')
           ->with(sprintf(FlagshipConstant::BUCKETING_API_CONTEXT_URL, $envId), [], $postBody)
            ->willReturnOnConsecutiveCalls(new HttpResponse(204, null), new HttpResponse(404, $errorMessage));

        $sdk = FlagshipConstant::FLAGSHIP_SDK;
        $logManagerStub->expects($this->once())->method('error')->with("[$sdk] " . $errorMessage);

        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($envId);
        $config->setLogManager($logManagerStub);

        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);

        $container = new Container();
        $configManager = new ConfigManager();
        $configManager->setConfig($config);
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $bucketingManager->getCampaignModifications($visitor);
        $bucketingManager->getCampaignModifications($visitor);
    }

    public function testSendContextWithError()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            ['error'],
            '',
            false
        );

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            ['post'],
            "",
            false
        );

        $envId = "envId";

        $visitorId = "visitor_1";
        $visitorContext = [
            "age" => 20,
            "sdk_osName" => PHP_OS,
            "sdk_deviceType" => "server",
            FlagshipConstant::FS_CLIENT => FlagshipConstant::SDK_LANGUAGE,
            FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
            FlagshipConstant::FS_USERS => $visitorId,
        ];

        $postBody = [
            "visitorId" => $visitorId,
            "type" => "CONTEXT",
            "data" => $visitorContext
        ];

        $exception = new Exception("test error");
        $httpClientMock->expects($this->exactly(1))
            ->method('post')
            ->with(sprintf(FlagshipConstant::BUCKETING_API_CONTEXT_URL, $envId), [], $postBody)
            ->willThrowException($exception);

        $logManagerStub->expects($this->once())->method('error');

        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($envId);
        $config->setLogManager($logManagerStub);
        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);

        $container = new Container();
        $configManager = new ConfigManager();
        $configManager->setConfig($config);
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $bucketingManager->getCampaignModifications($visitor);
    }

    public function testGetVariation()
    {
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig();
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);
        $visitorId = "123456";

        $getVariationMethod = Utils::getMethod($bucketingManager, "getVariation");

        //Test key Id  in variationGroup
        $variationGroups = [];
        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitorId);
        $this->assertCount(0, $variation);

        //Test key Id  in variationGroup
        $variations = [
            [
                "id" => "c20j8bk3fk9hdphqtd30",
                "modifications" => [
                    "type" => "HTML",
                    "value" => [
                        "my_html" => "<div>\n  <p>Original</p>\n</div>"
                    ]
                ],
                "allocation" => 34,
                "reference" => true
            ],
            [
                "id" => "c20j8bk3fk9hdphqtd3g",
                "modifications" => [
                    "type" => "HTML",
                    "value" => [
                        "my_html" => "<div>\n  <p>variation 1</p>\n</div>"
                    ]
                ],
                "allocation" => 33
            ],
            [
                "id" => "c20j9lgbcahhf2mvhbf0",
                "modifications" => [
                    "type" => "HTML",
                    "value" => [
                        "my_html" => "<div>\n  <p>variation 2</p>\n</div>"
                    ]
                ],
                "allocation" => 33
            ]
        ];
        $variationGroups = [
            FlagshipField::FIELD_ID => "9273BKSDJtoto",
            FlagshipField::FIELD_VARIATIONS => $variations
        ];
        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitorId);
        $this->assertSame($variations[0]['id'], $variation['id']);

        $variationGroups = [
            FlagshipField::FIELD_ID => "vgidéééà",
            FlagshipField::FIELD_VARIATIONS => $variations
        ];
        $visitorId = 'ëééééé';
        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitorId);
        $this->assertSame($variations[2]['id'], $variation['id']);
    }

    public function testIsMatchTargeting()
    {
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig();
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);
        $visitorId = "visitor_3";
        $visitorContext = [
            "age" => 20
        ];
        $container = new Container();
        $configManager = new ConfigManager();
        $configManager->setConfig($config);
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $isMatchTargetingMethod = Utils::getMethod($bucketingManager, "isMatchTargeting");

        $variationGroup = [];

        //Test key targeting variationGroup
        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test key targetingGroups in targeting
        $variationGroup = [
            FlagshipField::FIELD_TARGETING => [

            ]
        ];
        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test key targetings in targetingGroups
        $variationGroup = [
            FlagshipField::FIELD_TARGETING => [
                FlagshipField::FIELD_TARGETING_GROUPS => [
                    []
                ]
            ]
        ];
        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test not matching targetings

        $targetings = [
            "key" => "age",
            "operator" => "EQUALS",
            'value' => 21
        ];
        $variationGroup = [
            FlagshipField::FIELD_TARGETING => [
                FlagshipField::FIELD_TARGETING_GROUPS => [
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetings
                        ]
                    ]
                ]
            ]
        ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test matching targetings

        $targetings2 = [
            "key" => "age",
            "operator" => "EQUALS",
            'value' => 20
        ];

        $variationGroup = [
            FlagshipField::FIELD_TARGETING => [
                FlagshipField::FIELD_TARGETING_GROUPS => [
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetings2
                        ]
                    ]
                ]
            ]
        ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertTrue($output);


        //Test Many targetingGroups with one match

        $targetings2 = [
            "key" => "age",
            "operator" => "EQUALS",
            'value' => 22
        ];

        $targetingAllUsers = [
            "key" => "fs_all_users",
            "operator" => "EQUALS",
            'value' => ''
        ];

        $variationGroup = [
            FlagshipField::FIELD_TARGETING => [
                FlagshipField::FIELD_TARGETING_GROUPS => [
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetings
                        ]
                    ],
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetings2
                        ]
                    ],
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetingAllUsers
                        ]
                    ]
                ]
            ]
        ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertTrue($output);

        //Test Many targetingGroups with all false

        $targetings2 = [
            "key" => "age",
            "operator" => "EQUALS",
            'value' => 22
        ];

        $variationGroup = [
            FlagshipField::FIELD_TARGETING => [
                FlagshipField::FIELD_TARGETING_GROUPS => [
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetings
                        ]
                    ],
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetings2
                        ]
                    ],
                    [
                        FlagshipField::FIELD_TARGETINGS => [
                            $targetings2
                        ]
                    ]
                ]
            ]
        ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);
    }

    public function testCheckAndTargeting()
    {
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig();
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);
        $visitorId = "visitor_3";
        $visitorContext = [
            "age" => 20
        ];
        $container = new Container();
        $configManager = new ConfigManager();
        $configManager->setConfig($config);
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $checkAndTargetingMethod = Utils::getMethod($bucketingManager, "checkAndTargeting");

        //test key = fs_all_users
        $targetingAllUsers = [
            "key" => "fs_all_users",
            "operator" => "EQUALS",
            'value' => ''
        ];

        $innerTargetings = [$targetingAllUsers];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //test key = fs_all_users and not match key
        $innerTargetings = [$targetingAllUsers,[
            "notMatchKey" => "anyValue",
            "operator" => "EQUALS",
            'value' => ''
        ]];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);

        //test key = fs_users
        $targetingFsUsers = [
            "key" => "fs_users",
            "operator" => "EQUALS",
            'value' => $visitorId
        ];

        $innerTargetings = [$targetingFsUsers];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //test key not match context
        $targetingKeyContext = [
            "key" => "anyKey",
            "operator" => "EQUALS",
            'value' => "anyValue"
        ];

        $innerTargetings = [$targetingKeyContext];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);

        //test key match context
        $targetingKeyContext = [
            "key" => "age",
            "operator" => "EQUALS",
            'value' => 20
        ];

        $innerTargetings = [$targetingKeyContext];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //test key match context with different value
        $targetingKeyContext2 = [
            "key" => "age",
            "operator" => "EQUALS",
            'value' => 21
        ];

        $innerTargetings = [$targetingKeyContext2];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);

        //And logic
        //All true
        $innerTargetings = [$targetingAllUsers, $targetingFsUsers, $targetingKeyContext];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //Test one false
        $innerTargetings = [$targetingAllUsers, $targetingFsUsers, $targetingKeyContext2];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);
    }

    public function testOperator()
    {
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig();
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);
        $visitorId = "visitor_3";
        $visitorContext = [
//            "isPHP" => true
            "age" => 20
        ];
        $container = new Container();
        $configManager = new ConfigManager();
        $configManager->setConfig($config);

        $testOperatorMethod = Utils::getMethod($bucketingManager, "testOperator");

        /*Test EQUALS*/

        //Test different values
        $contextValue = 5;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test different type
        $contextValue = 5;
        $targetingValue = "5";
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test same type
        $contextValue = 5;
        $targetingValue = 5;
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        $contextValue = 5;
        $targetingValue = [5,1,2,3];
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        /* Test NOT_EQUALS */

        //Test different values
        $contextValue = 5;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        $contextValue = 5;
        $targetingValue = [6,1,2,3];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test different type
        $contextValue = 5;
        $targetingValue = "5";
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test same type
        $contextValue = 5;
        $targetingValue = 5;
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        $contextValue = 5;
        $targetingValue = [1,2,3,5,6];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test CONTAINS */

        //Test contextValue not contains targetingValue
        $contextValue = 5;
        $targetingValue = [8, 7, 4, 1];
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue contains targetingValue
        $contextValue = 5;
        $targetingValue = [8, 7, 5, 1];
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue
        $contextValue = "nopq_hij";
        $targetingValue = ["abc", "dfg", "hij", "klm"];
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue
        $contextValue = "nopq_hij";
        $targetingValue = "hij";
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue
        $contextValue = "nopq_hij";
        $targetingValue = "hidf";
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test NOT_CONTAINS */

        //Test contextValue not contains targetingValue
        $contextValue = 5;
        $targetingValue = [8, 7, 4, 1];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue
        $contextValue = 5;
        $targetingValue = [8, 7, 5, 1];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue contains targetingValue
        $contextValue = "nopq_hij";
        $targetingValue = ["abc", "dfg", "hij", "klm"];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test GREATER_THAN */

        //Test contextValue not GREATER_THAN targetingValue
        $contextValue = 5;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke($bucketingManager, 'GREATER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue not GREATER_THAN targetingValue
        $contextValue = 5;
        $targetingValue = 5;
        $output = $testOperatorMethod->invoke($bucketingManager, 'GREATER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue not GREATER_THAN targetingValue
        $contextValue = 'a';
        $targetingValue = 'b';
        $output = $testOperatorMethod->invoke($bucketingManager, 'GREATER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue not GREATER_THAN targetingValue
        $contextValue = 'abz';
        $targetingValue = 'bcg';
        $output = $testOperatorMethod->invoke($bucketingManager, 'GREATER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue GREATER_THAN targetingValue
        $contextValue = 8;
        $targetingValue = 2;
        $output = $testOperatorMethod->invoke($bucketingManager, 'GREATER_THAN', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue GREATER_THAN targetingValue
        $contextValue = "9dlk";
        $targetingValue = 8;
        $output = $testOperatorMethod->invoke($bucketingManager, 'GREATER_THAN', $contextValue, $targetingValue);
        $this->assertTrue($output);

        /* Test LOWER_THAN */

        //Test contextValue LOWER_THAN targetingValue
        $contextValue = 5;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke($bucketingManager, 'LOWER_THAN', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue not GREATER_THAN targetingValue
        $contextValue = 5;
        $targetingValue = 5;
        $output = $testOperatorMethod->invoke($bucketingManager, 'LOWER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue LOWER_THAN targetingValue
        $contextValue = 'a';
        $targetingValue = 'b';
        $output = $testOperatorMethod->invoke($bucketingManager, 'LOWER_THAN', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue LOWER_THAN targetingValue
        $contextValue = 'abz';
        $targetingValue = 'bcg';
        $output = $testOperatorMethod->invoke($bucketingManager, 'LOWER_THAN', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue not LOWER_THAN targetingValue
        $contextValue = 8;
        $targetingValue = 2;
        $output = $testOperatorMethod->invoke($bucketingManager, 'LOWER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue not LOWER_THAN targetingValue
        $contextValue = "9dlk";
        $targetingValue = 8;
        $output = $testOperatorMethod->invoke($bucketingManager, 'LOWER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test GREATER_THAN_OR_EQUALS */

        //Test contextValue GREATER_THAN targetingValue
        $contextValue = 8;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'GREATER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertTrue($output);

        //Test contextValue EQUALS targetingValue
        $contextValue = 8;
        $targetingValue = 8;
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'GREATER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertTrue($output);

        //Test contextValue LOWER_THAN targetingValue
        $contextValue = 7;
        $targetingValue = 8;
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'GREATER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertFalse($output);

        //Test contextValue LOWER_THAN targetingValue
        $contextValue = 'a';
        $targetingValue = 'b';
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'GREATER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertFalse($output);

        /* Test LOWER_THAN_OR_EQUALS */

        //Test contextValue GREATER_THAN targetingValue
        $contextValue = 8;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'LOWER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertFalse($output);

        //Test contextValue EQUALS targetingValue
        $contextValue = 8;
        $targetingValue = 8;
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'LOWER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertTrue($output);

        //Test contextValue LOWER_THAN targetingValue
        $contextValue = 7;
        $targetingValue = 8;
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'LOWER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertTrue($output);

        //Test contextValue LOWER_THAN targetingValue
        $contextValue = 'a';
        $targetingValue = 'b';
        $output = $testOperatorMethod->invoke(
            $bucketingManager,
            'LOWER_THAN_OR_EQUALS',
            $contextValue,
            $targetingValue
        );
        $this->assertTrue($output);

        /* Test STARTS_WITH */

        //Test contextValue STARTS_WITH targetingValue
        $contextValue = "abcd";
        $targetingValue = "ab";
        $output = $testOperatorMethod->invoke($bucketingManager, 'STARTS_WITH', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue not STARTS_WITH targetingValue
        $contextValue = "abcd";
        $targetingValue = "bc";
        $output = $testOperatorMethod->invoke($bucketingManager, 'STARTS_WITH', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test ENDS_WITH */

        //Test contextValue ENDS_WITH targetingValue
        $contextValue = "abcd";
        $targetingValue = "d";
        $output = $testOperatorMethod->invoke($bucketingManager, 'ENDS_WITH', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue not ENDS_WITH targetingValue
        $contextValue = "abcd";
        $targetingValue = "ab";
        $output = $testOperatorMethod->invoke($bucketingManager, 'ENDS_WITH', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test Any operator else
        $contextValue = "abcd";
        $targetingValue = "ab";
        $output = $testOperatorMethod->invoke($bucketingManager, 'ANY', $contextValue, $targetingValue);
        $this->assertFalse($output);
    }
}
