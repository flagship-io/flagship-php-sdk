<?php

namespace Flagship\Decision;

use DateTime;
use Exception;
use ReflectionException;
use Flagship\Utils\Utils;
use Psr\Log\LoggerInterface;
use Flagship\Utils\Container;
use Flagship\Utils\HttpClient;
use Flagship\Utils\MurmurHash;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipField;
use Flagship\Model\HttpResponse;
use Flagship\Utils\ConfigManager;
use Flagship\Enum\FlagshipConstant;
use Flagship\Config\BucketingConfig;
use Flagship\Visitor\DefaultStrategy;
use Flagship\Visitor\VisitorDelegate;
use Flagship\Visitor\StrategyAbstract;
use Flagship\Enum\TroubleshootingLabel;
use PHPUnit\Framework\MockObject\MockObject;

class BucketingManagerTest extends TestCase
{
    public function testGetCampaignModification()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $trackingManagerMock = $this->getMockForAbstractClass("Flagship\Api\TrackingManagerInterface");

        $bucketingUrl = "127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);
        $bucketingManager->setFlagshipInstanceId("flagship_instance_id");
        $bucketingManager->setTrackingManager($trackingManagerMock);
        $visitorId = "visitor_1";
        $visitorContext = ["age" => 20];
        $container = new Container();
        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();
        $configManager->setConfig($config);

        $visitor = $this->getMockBuilder(VisitorDelegate::class)->setConstructorArgs([$container, $configManager, $visitorId, false, $visitorContext, true])->onlyMethods(["sendHit"])->getMock();

        $bucketingFile = \file_get_contents(__DIR__ . '/bucketing.json');
        $httpClientMock->expects($this->exactly(6))->method('get')->with($bucketingUrl)->willReturnOnConsecutiveCalls(
            new HttpResponse(204, null),
            new HttpResponse(204, json_decode('{"panic": true}', true)),
            new HttpResponse(204, json_decode('{}', true)),
            new HttpResponse(204, json_decode('{"campaigns":[{}]}', true)),
            new HttpResponse(204, json_decode('{"notExistKey": false}', true)),
            new HttpResponse(204, json_decode($bucketingFile, true))
        );

        //Test File not exist
        $campaigns = $bucketingManager->getCampaignFlags($visitor);

        $this->assertCount(0, $campaigns);

        //Test Panic Mode
        $campaigns = $bucketingManager->getCampaignFlags($visitor);

        $this->assertCount(0, $campaigns);

        //Test campaign property
        $campaigns = $bucketingManager->getCampaignFlags($visitor);

        $this->assertCount(0, $campaigns);

        //Test campaign[FIELD_VARIATION_GROUPS]

        $campaigns = $bucketingManager->getCampaignFlags($visitor);

        $this->assertCount(0, $campaigns);

        //

        $campaigns = $bucketingManager->getCampaignFlags($visitor);

        $this->assertCount(0, $campaigns);

        //
        $campaigns = $bucketingManager->getCampaignFlags($visitor);

        $this->assertCount(6, $campaigns);

        //test invalid bucketing file url

        $config->setSyncAgentUrl("");
        $campaigns = $bucketingManager->getCampaignFlags($visitor);

        $this->assertCount(0, $campaigns);
    }

    /**
     * @throws Exception
     */
    public function testGetTroubleshootingData()
    {
        $httpClientMock = $this->getMockForAbstractClass('Flagship\Utils\HttpClientInterface');

        $trackingManagerMock = $this->getMockForAbstractClass("Flagship\Api\TrackingManagerInterface");

        $bucketingUrl = "127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);
        $bucketingManager->setFlagshipInstanceId("flagship_instance_id");
        $bucketingManager->setTrackingManager($trackingManagerMock);
        $visitorId = "visitor_1";
        $visitorContext = ["age" => 20];
        $container = new Container();
        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();

        $visitor = $this->getMockBuilder(VisitorDelegate::class)->setConstructorArgs([$container, $configManager, $visitorId, false, $visitorContext, true])->onlyMethods(["sendHit"])->getMock();

        $bucketingFile = \file_get_contents(__DIR__ . '/bucketing.json');
        $bucketingContent = json_decode($bucketingFile, true);
        $troubleshooting = [
                            "startDate" => "2023-04-13T09:33:38.049Z",
                            "endDate"   => "2023-04-13T10:03:38.049Z",
                            "timezone"  => "Europe/Paris",
                            "traffic"   => 40,
                           ];
        $bucketingContent["accountSettings"] = ["troubleshooting" => $troubleshooting];

        $matcher = $this->exactly(1);
        $trackingManagerMock->expects($matcher)->method('addTroubleshootingHit')->with($this->callback(function ($param) use ($matcher) {
                return $param->getLabel() === TroubleshootingLabel::SDK_BUCKETING_FILE;
        }));

        $httpClientMock->expects($this->exactly(1))->method('get')->with($bucketingUrl)->willReturnOnConsecutiveCalls(
            new HttpResponse(204, $bucketingContent)
        );

        $bucketingManager->getCampaignFlags($visitor);

        $troubleshootingData = $bucketingManager->getTroubleshootingData();
        $this->assertSame($troubleshooting['traffic'], $troubleshootingData->getTraffic());
        $startDate = new DateTime($troubleshooting['startDate']);
        $this->assertSame($startDate->getTimestamp(), $troubleshootingData->getStartDate()->getTimestamp());
        $endDate = new DateTime($troubleshooting['endDate']);
        $this->assertSame($endDate->getTimestamp(), $troubleshootingData->getEndDate()->getTimestamp());
        $this->assertSame($troubleshooting['timezone'], $troubleshootingData->getTimezone());
    }

    public function testSendContext()
    {
        $logManagerStub = $this->getMockForAbstractClass(
            LoggerInterface::class,
            ['error'],
            '',
            false
        );

        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            [
             'post',
             'get',
            ],
            "",
            false
        );

        $trackerManager = $this->getMockForAbstractClass(
            'Flagship\Api\TrackingManagerAbstract',
            ['addHit'],
            '',
            false
        );

        $trackingManagerMock = $this->getMockForAbstractClass("Flagship\Api\TrackingManagerInterface");

        $containerMock = $this->getMockBuilder(
            'Flagship\Utils\Container'
        )->onlyMethods(['get'])->disableOriginalConstructor()->getMock();

        $containerGetMethod = function ($arg1, $arg2) {

            return new DefaultStrategy($arg2[0]);
        };

        $containerMock->method('get')->will($this->returnCallback($containerGetMethod));

        $envId = "envId";

        $visitorId = "visitor_1";
        $visitorContext = [
                           "age"                        => 20,
                           "sdk_osName"                 => PHP_OS,
                           "sdk_deviceType"             => "server",
                           FlagshipConstant::FS_CLIENT  => FlagshipConstant::SDK_LANGUAGE,
                           FlagshipConstant::FS_VERSION => FlagshipConstant::SDK_VERSION,
                           FlagshipConstant::FS_USERS   => $visitorId,
                          ];


        $bucketingUrl  = "http:127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl, $envId);
        $config->setLogManager($logManagerStub);

        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);
        $bucketingManager->setFlagshipInstanceId("flagship_instance_id");

        $bucketingManager->setTrackingManager($trackingManagerMock);

        /**
         * @var MockObject|ConfigManager $configManager
         */
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->setConfig($config)->setTrackingManager($trackerManager);


        /**
         * @var MockObject|VisitorDelegate $visitor
         */
        $visitor = $this->getMockBuilder(VisitorDelegate::class)
            ->setConstructorArgs([$containerMock, $configManager, $visitorId, false, $visitorContext, true])
            ->onlyMethods(["sendHit"])
            ->getMock();

        $httpClientMock->expects($this->exactly(4))
            ->method('get')
            ->willReturn(
                new HttpResponse(204, json_decode('{"campaigns":[{}]}', true))
            );

        $visitor->expects($this->exactly(2))->method("sendHit");

        $bucketingManager->getCampaignFlags($visitor);

        $bucketingManager->getCampaignFlags($visitor);

        $visitor->updateContext("new_context", "new_value");


        $bucketingManager->getCampaignFlags($visitor);

        //Test empty context
        $visitor->clearContext();
        $visitor = new VisitorDelegate($containerMock, $configManager, $visitorId, false, [], true);
        $bucketingManager->getCampaignFlags($visitor);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetVariation()
    {
        $bucketingUrl  = "http:127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);
        $visitorId = "123456";

        $container = new Container();
        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();
        $configManager->setConfig($config);
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, [], true);

        $getVariationMethod = Utils::getMethod(BucketingManager::class, "getVariation");

        //Test key id  in variationGroup
        $variationGroups = [];
        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitor);
        $this->assertCount(0, $variation);

        //Test key id  in variationGroup
        $variations = [
                       [
                        "id"            => "c20j8bk3fk9hdphqtd30",
                        "name"          => "variation1",
                        "modifications" => [
                                            "type"  => "HTML",
                                            "value" => ["my_html" => "<div>\n  <p>Original</p>\n</div>"],
                                           ],
                        "allocation"    => 34,
                        "reference"     => true,
                       ],
                       [
                        "id"            => "c20j8bk3fk9hdphqtd3g",
                        "name"          => "variation2",
                        "modifications" => [
                                            "type"  => "HTML",
                                            "value" => ["my_html" => "<div>\n  <p>variation 1</p>\n</div>"],
                                           ],
                        "allocation"    => 33,
                       ],
                       [
                        "id"            => "c20j9lgbcahhf2mvhbf0",
                        "name"          => "variation3",
                        "modifications" => [
                                            "type"  => "HTML",
                                            "value" => ["my_html" => "<div>\n  <p>variation 2</p>\n</div>"],
                                           ],
                        "allocation"    => 33,
                       ],
                      ];
        $variationGroups = [
                            FlagshipField::FIELD_ID         => "9273BKSDJtoto",
                            FlagshipField::FIELD_VARIATIONS => $variations,
                            FlagshipField::FIELD_NANE       => "varGroupName",
                           ];
        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitor);
        $this->assertSame($variations[0]['id'], $variation['id']);

        $variationGroups = [
                            FlagshipField::FIELD_ID         => "vgidéééà",
                            FlagshipField::FIELD_VARIATIONS => $variations,
                           ];
        $visitorId = 'ëééééé';
        $visitor->setVisitorId($visitorId);
        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitor);
        $this->assertSame($variations[2]['id'], $variation['id']);

        //Test realloc
        $realloCvariations = [
                              [
                               "id"            => "c20j8bk3fk9hdphqtd30",
                               "name"          => "variation1",
                               "modifications" => [
                                                   "type"  => "HTML",
                                                   "value" => ["my_html" => "<div>\n  <p>Original</p>\n</div>"],
                                                  ],
                               "allocation"    => 100,
                               "reference"     => true,
                              ],
                              [
                               "id"            => "c20j8bk3fk9hdphqtd3g",
                               "name"          => "variation2",
                               "modifications" => [
                                                   "type"  => "HTML",
                                                   "value" => ["my_html" => "<div>\n  <p>variation 1</p>\n</div>"],
                                                  ],
                               "allocation"    => 0,
                              ],
                              [
                               "id"            => "c20j9lgbcahhf2mvhbf0",
                               "name"          => "variation2",
                               "modifications" => [
                                                   "type"  => "HTML",
                                                   "value" => ["my_html" => "<div>\n  <p>variation 2</p>\n</div>"],
                                                  ],
                               "allocation"    => 0,
                              ],
                             ];


        $variationGroups = [
                            FlagshipField::FIELD_ID         => "9273BKSDJtoto",
                            FlagshipField::FIELD_VARIATIONS => $realloCvariations,
                           ];
        $assignmentsHistory = ["9273BKSDJtoto" => "c20j9lgbcahhf2mvhbf0"];
        $visitorCache = [
                         StrategyAbstract::VERSION => 1,
                         StrategyAbstract::DATA    => [StrategyAbstract::ASSIGNMENTS_HISTORY => $assignmentsHistory],
                        ];

        $visitor->visitorCache = $visitorCache;

        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitor);

        $this->assertSame($realloCvariations[2]['id'], $variation['id']);

        //Test deleted variation

        $reallovariations = [
                             [
                              "id"            => "c20j8bk3fk9hdphqtd30",
                              "modifications" => [
                                                  "type"  => "HTML",
                                                  "value" => ["my_html" => "<div>\n  <p>Original</p>\n</div>"],
                                                 ],
                              "allocation"    => 50,
                              "reference"     => true,
                             ],
                             [
                              "id"            => "c20j8bk3fk9hdphqtd3g",
                              "modifications" => [
                                                  "type"  => "HTML",
                                                  "value" => ["my_html" => "<div>\n  <p>variation 1</p>\n</div>"],
                                                 ],
                              "allocation"    => 50,
                             ],
                            ];


        $variationGroups = [
                            FlagshipField::FIELD_ID         => "9273BKSDJtoto",
                            FlagshipField::FIELD_VARIATIONS => $reallovariations,
                           ];


        $visitor->visitorCache = $visitorCache;

        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitor);

        $this->assertCount(0, $variation);

        //
        $realloCvariations = [
            [
                "id" => "c20j8bk3fk9hdphqtd30",
                "name" => "variation1",
                "modifications" => [
                    "type" => "HTML",
                    "value" => [
                        "my_html" => "<div>\n  <p>Original</p>\n</div>"
                    ]
                ],
                "allocation" => 0,
                "reference" => true
            ],
            [
                "id" => "c20j8bk3fk9hdphqtd3g",
                "name" => "variation2",
                "modifications" => [
                    "type" => "HTML",
                    "value" => [
                        "my_html" => "<div>\n  <p>variation 1</p>\n</div>"
                    ]
                ],
                "allocation" => 0
            ],
            [
                "id" => "c20j9lgbcahhf2mvhbf0",
                "name" => "variation2",
                "modifications" => [
                    "type" => "HTML",
                    "value" => [
                        "my_html" => "<div>\n  <p>variation 2</p>\n</div>"
                    ]
                ],
                "allocation" => 100
            ]
        ];


        $variationGroups = [
            FlagshipField::FIELD_ID => "9273BKSDJtoto",
            FlagshipField::FIELD_VARIATIONS => $realloCvariations
        ];
        $assignmentsHistory = [];
        $visitorCache = [
            StrategyAbstract::VERSION => 1,
            StrategyAbstract::DATA => [
                StrategyAbstract::ASSIGNMENTS_HISTORY =>  $assignmentsHistory
            ]
        ];

        $visitor->visitorCache = $visitorCache;

        $variation = $getVariationMethod->invoke($bucketingManager, $variationGroups, $visitor);

        $this->assertNotSame($realloCvariations[0]['id'], $variation['id']);
        $this->assertNotSame($realloCvariations[1]['id'], $variation['id']);
        $this->assertSame($realloCvariations[2]['id'], $variation['id']);
    }

    /**
     * @throws ReflectionException
     */
    public function testIsMatchTargeting()
    {
        $bucketingUrl  = "http:127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);
        $visitorId = "visitor_3";
        $visitorContext = ["age" => 20];
        $container = new Container();
        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();
        $configManager->setConfig($config);
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $isMatchTargetingMethod = Utils::getMethod(BucketingManager::class, "isMatchTargeting");

        $variationGroup = [];

        //Test key targeting variationGroup
        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test key targetingGroups in targeting
        $variationGroup = [
            FlagshipField::FIELD_TARGETING => []
        ];
        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test key targetings in targetingGroups
        $variationGroup = [
                           FlagshipField::FIELD_TARGETING => [
                                                              FlagshipField::FIELD_TARGETING_GROUPS => [
                                                                                                        [],
                                                                                                       ],
                                                             ],
                          ];
        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test not matching targetings

        $targetings = [
                       "key"      => "age",
                       "operator" => "EQUALS",
                       'value'    => 21,
                      ];
        $variationGroup = [
                           FlagshipField::FIELD_TARGETING => [
                                                              FlagshipField::FIELD_TARGETING_GROUPS => [
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetings],
                                                                                                        ],
                                                                                                       ],
                                                             ],
                          ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);

        //Test matching targetings

        $targetings2 = [
                        "key"      => "age",
                        "operator" => "EQUALS",
                        'value'    => 20,
                       ];

        $variationGroup = [
                           FlagshipField::FIELD_TARGETING => [
                                                              FlagshipField::FIELD_TARGETING_GROUPS => [
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetings2],
                                                                                                        ],
                                                                                                       ],
                                                             ],
                          ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertTrue($output);


        //Test Many targetingGroups with one match

        $targetings2 = [
                        "key"      => "age",
                        "operator" => "EQUALS",
                        'value'    => 22,
                       ];

        $targetingAllUsers = [
                              "key"      => "fs_all_users",
                              "operator" => "EQUALS",
                              'value'    => '',
                             ];

        $variationGroup = [
                           FlagshipField::FIELD_TARGETING => [
                                                              FlagshipField::FIELD_TARGETING_GROUPS => [
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetings],
                                                                                                        ],
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetings2],
                                                                                                        ],
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetingAllUsers],
                                                                                                        ],
                                                                                                       ],
                                                             ],
                          ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertTrue($output);

        //Test Many targetingGroups with all false

        $variationGroup = [
                           FlagshipField::FIELD_TARGETING => [
                                                              FlagshipField::FIELD_TARGETING_GROUPS => [
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetings],
                                                                                                        ],
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetings2],
                                                                                                        ],
                                                                                                        [
                                                                                                         FlagshipField::FIELD_TARGETINGS => [$targetings2],
                                                                                                        ],
                                                                                                       ],
                                                             ],
                          ];

        $output = $isMatchTargetingMethod->invoke($bucketingManager, $variationGroup, $visitor);
        $this->assertFalse($output);
    }

    /**
     * @throws ReflectionException
     */
    public function testCheckAndTargeting()
    {
        $bucketingUrl  = "http:127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);
        $visitorId = "visitor_3";
        $visitorContext = ["age" => 20];
        $container = new Container();
        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();
        $configManager->setConfig($config);
        $visitor = new VisitorDelegate($container, $configManager, $visitorId, false, $visitorContext, true);

        $checkAndTargetingMethod = Utils::getMethod(BucketingManager::class, "checkAndTargeting");

        //test key = fs_all_users
        $targetingAllUsers = [
                              "key"      => "fs_all_users",
                              "operator" => "EQUALS",
                              'value'    => '',
                             ];

        $innerTargetings = [$targetingAllUsers];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //test key = fs_all_users and not match key
        $innerTargetings = [$targetingAllUsers, [
            "key" => "anyValue",
            "operator" => "EQUALS",
            'value' => ''
        ]];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);

        //Test operator EXISTS when context doesn't exist

        $innerTargetingsExists = [$targetingAllUsers, [
            "operator" => "EXISTS",
            "key" => "mixpanel::city",
            "value" => true,
            "provider" => "mixpanel"
        ]];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetingsExists, $visitor);
        $this->assertFalse($output);

        //Test operator EXISTS when context  exists

        $visitor->updateContext("mixpanel::city", false);
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetingsExists, $visitor);
        $this->assertTrue($output);

        //Test operator NOT_EXISTS when context  exists

        $innerTargetingsExists = [$targetingAllUsers, [
            "operator" => "NOT_EXISTS",
            "key" => "mixpanel::city",
            "value" => true,
            "provider" => "mixpanel"
        ]];

        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetingsExists, $visitor);
        $this->assertFalse($output);

        //Test operator NOT_EXISTS when context doesn't exist

        $innerTargetingsExists = [$targetingAllUsers, [
            "operator" => "NOT_EXISTS",
            "key" => "mixpanel::genre",
            "value" => true,
            "provider" => "mixpanel"
        ]];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetingsExists, $visitor);
        $this->assertTrue($output);

        //test key = fs_users
        $targetingFsUsers = [
                             "key"      => "fs_users",
                             "operator" => "EQUALS",
                             'value'    => $visitorId,
                            ];

        $innerTargetings = [$targetingFsUsers];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //test key not match context
        $targetingKeyContext = [
                                "key"      => "anyKey",
                                "operator" => "EQUALS",
                                'value'    => "anyValue",
                               ];

        $innerTargetings = [$targetingKeyContext];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);

        //test key match context
        $targetingKeyContext = [
                                "key"      => "age",
                                "operator" => "EQUALS",
                                'value'    => 20,
                               ];

        $innerTargetings = [$targetingKeyContext];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //test key match context with different value
        $targetingKeyContext2 = [
                                 "key"      => "age",
                                 "operator" => "EQUALS",
                                 'value'    => 21,
                                ];

        $innerTargetings = [$targetingKeyContext2];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);

        //And logic
        //All true
        $innerTargetings = [
                            $targetingAllUsers,
                            $targetingFsUsers,
                            $targetingKeyContext,
                           ];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertTrue($output);

        //Test one false
        $innerTargetings = [
                            $targetingAllUsers,
                            $targetingFsUsers,
                            $targetingKeyContext2,
                           ];
        $output = $checkAndTargetingMethod->invoke($bucketingManager, $innerTargetings, $visitor);
        $this->assertFalse($output);
    }

    /**
     * @throws ReflectionException
     */
    public function testOperator()
    {
        $bucketingUrl  = "http:127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $bucketingManager = new BucketingManager(new HttpClient(), $config, $murmurhash);

        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();
        $configManager->setConfig($config);

        $testOperatorMethod = Utils::getMethod(BucketingManager::class, "testOperator");

        /*Test EQUALS*/

        //Test different values
        $contextValue = 5;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test different type

        $targetingValue = "5";
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test same type

        $targetingValue = 5;
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);


        $targetingValue = [5, 1, 2, 3];
        $output = $testOperatorMethod->invoke($bucketingManager, 'EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        /* Test NOT_EQUALS */

        //Test different values

        $targetingValue = 6;
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);


        $targetingValue = [6, 1, 2, 3];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test different type

        $targetingValue = "5";
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test same type

        $targetingValue = 5;
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        $targetingValue = [1, 2, 3, 5, 6];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_EQUALS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test CONTAINS */

        //Test contextValue not contains targetingValue

        $targetingValue = [
                           8,
                           7,
                           4,
                           1,
                          ];
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue contains targetingValue

        $targetingValue = [
                           8,
                           7,
                           5,
                           1,
                          ];
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue
        $contextValue = "nopq_hij";
        $targetingValue = [
                           "abc",
                           "dfg",
                           "hij",
                           "klm",
                          ];
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue

        $targetingValue = "hij";
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue

        $targetingValue = "hidf";
        $output = $testOperatorMethod->invoke($bucketingManager, 'CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test NOT_CONTAINS */

        //Test contextValue not contains targetingValue
        $contextValue = 5;
        $targetingValue = [
                           8,
                           7,
                           4,
                           1,
                          ];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_CONTAINS', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue contains targetingValue

        $targetingValue = [
                           8,
                           7,
                           5,
                           1,
                          ];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue contains targetingValue
        $contextValue = "nopq_hij";
        $targetingValue = [
                           "abc",
                           "dfg",
                           "hij",
                           "klm",
                          ];
        $output = $testOperatorMethod->invoke($bucketingManager, 'NOT_CONTAINS', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test GREATER_THAN */

        //Test contextValue not GREATER_THAN targetingValue
        $contextValue = 5;
        $targetingValue = 6;
        $output = $testOperatorMethod->invoke($bucketingManager, 'GREATER_THAN', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test contextValue not GREATER_THAN targetingValue

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

        $targetingValue = "bc";
        $output = $testOperatorMethod->invoke($bucketingManager, 'STARTS_WITH', $contextValue, $targetingValue);
        $this->assertFalse($output);

        /* Test ENDS_WITH */

        //Test contextValue ENDS_WITH targetingValue

        $targetingValue = "d";
        $output = $testOperatorMethod->invoke($bucketingManager, 'ENDS_WITH', $contextValue, $targetingValue);
        $this->assertTrue($output);

        //Test contextValue not ENDS_WITH targetingValue

        $targetingValue = "ab";
        $output = $testOperatorMethod->invoke($bucketingManager, 'ENDS_WITH', $contextValue, $targetingValue);
        $this->assertFalse($output);

        //Test Any operator else

        $output = $testOperatorMethod->invoke($bucketingManager, 'ANY', $contextValue, $targetingValue);
        $this->assertFalse($output);
    }

    public function testGetThirdPartySegment()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            [],
            "",
            false,
            false,
            true,
            [
             'post',
             'get',
            ]
        );

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $trackingManagerMock = $this->getMockForAbstractClass("Flagship\Api\TrackingManagerInterface");

        $bucketingUrl = "127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $config->setEnvId("env_id")->setFetchThirdPartyData(true)->setLogManager($logManagerStub);

        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);
        $bucketingManager->setFlagshipInstanceId("instance_id");

        $bucketingManager->setTrackingManager($trackingManagerMock);
        $visitorId = "visitor_1";
        $visitorContext = ["age" => 20];

        $container = new Container();
        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();
        $configManager->setConfig($config);

        $visitor = $this->getMockBuilder(VisitorDelegate::class)->setConstructorArgs([$container, $configManager, $visitorId, false, $visitorContext, true])->onlyMethods(["sendHit"])->getMock();

        $segments = [
                     [
                      'visitor_id' => 'wonderful_visitor_1',
                      'segment'    => 'gender',
                      'value'      => '',
                      'expiration' => 1689771307,
                      'partner'    => 'facebook',
                     ],
                     [
                      'visitor_id' => 'wonderful_visitor_1',
                      'segment'    => 'generation',
                      'value'      => '',
                      'expiration' => 1689771307,
                      'partner'    => 'facebook',
                     ],
                     [
                      'visitor_id' => 'wonderful_visitor_1',
                      'segment'    => 'city',
                      'value'      => 'london',
                      'expiration' => 1689771117,
                      'partner'    => 'mixpanel',
                     ],
                     [
                      'visitor_id' => 'wonderful_visitor_1',
                      'segment'    => 'device',
                      'value'      => 'firefox',
                      'expiration' => 1689771117,
                      'partner'    => 'mixpanel',
                     ],
                     [
                      'visitor_id' => 'wonderful_visitor_1',
                      'segment'    => 'gender',
                      'value'      => 'female',
                      'expiration' => 1689771007,
                      'partner'    => 'segmentio',
                     ],
                     [
                      'visitor_id' => 'wonderful_visitor_1',
                      'segment'    => 'generation',
                      'value'      => 'gen-z',
                      'expiration' => 1689771007,
                      'partner'    => 'segmentio',
                     ],
                    ];

        $segmentUrl = sprintf(FlagshipConstant::THIRD_PARTY_SEGMENT_URL, $config->getEnvId(), $visitorId);
        $campaigns = ["campaigns" => []];

        $matcher = $this->exactly(2);
        $httpClientMock->expects($matcher)->method("get")->with(
            $this->logicalOr(
                $this->equalTo($bucketingUrl),
                $this->equalTo($segmentUrl)
            ),
            $this->equalTo([])
        )->willReturnOnConsecutiveCalls(new HttpResponse(200, $campaigns, []), new HttpResponse(200, $segments, []));

        $bucketingManager->getCampaigns($visitor);
        $context = $visitor->getContext();

        foreach ($segments as $item) {
            $key = $item[BucketingManager::PARTNER] . "::" . $item[BucketingManager::SEGMENT];
            $this->assertArrayHasKey($key, $context);
            $this->assertSame($item[BucketingManager::VALUE], $context[$key]);
        }
    }

    public function testGetThirdPartySegmentException()
    {
        $httpClientMock = $this->getMockForAbstractClass(
            'Flagship\Utils\HttpClientInterface',
            [],
            "",
            false,
            false,
            true,
            [
             'post',
             'get',
            ]
        );

        $logManagerStub = $this->getMockForAbstractClass(
            'Psr\Log\LoggerInterface',
            [],
            "",
            true,
            true,
            true,
            ['error']
        );

        $trackingManagerMock = $this->getMockForAbstractClass("Flagship\Api\TrackingManagerInterface");

        $bucketingUrl = "127.0.0.1:3000";
        $murmurhash = new MurmurHash();
        $config = new BucketingConfig($bucketingUrl);
        $config->setEnvId("env_id")->setFetchThirdPartyData(true);

        $bucketingManager = new BucketingManager($httpClientMock, $config, $murmurhash);
        $bucketingManager->setTrackingManager($trackingManagerMock);
        $bucketingManager->setFlagshipInstanceId("instance_id");

        $visitorId = "visitor_1";
        $visitorContext = ["age" => 20];
        $container = new Container();

        $configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();

        $configManager->setConfig($config);

        $visitor = $this->getMockBuilder(VisitorDelegate::class)->setConstructorArgs([$container, $configManager, $visitorId, false, $visitorContext, true])->onlyMethods(["sendHit"])->getMock();

        $segmentUrl = sprintf(FlagshipConstant::THIRD_PARTY_SEGMENT_URL, $config->getEnvId(), $visitorId);
        $campaigns = ["campaigns" => []];

        $matcher = $this->exactly(2);
        $httpClientMock->expects($matcher)->method("get")->with(
            $this->logicalOr(
                $this->equalTo($bucketingUrl),
                $this->equalTo($segmentUrl)
            ),
            $this->equalTo([])
        )->willReturnOnConsecutiveCalls(
            new HttpResponse(200, $campaigns, []),
            $this->throwException(new Exception("error"))
        );

        $config->setLogManager($logManagerStub);

        $logManagerStub->expects($this->exactly(1))->method("error");

        $bucketingManager->getCampaigns($visitor);
    }
}
