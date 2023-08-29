<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Cache\IHitCacheImplementation;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\LogLevel;
use Flagship\Flagship;
use Flagship\Hit\Page;

$ENV_ID = '';
$API_KEY = '';
$BUCKETING_URL = 'http://127.0.0.1:3000/bucketing';

Flagship::start(
    $ENV_ID,
    $API_KEY,
    DecisionApiConfig::bucketing($BUCKETING_URL)
        ->setCacheStrategy(CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE)
        ->setFetchThirdPartyData(true)
        ->setTimeout(5000)
        ->setLogLevel(LogLevel::ALL)
);

$visitor = Flagship::newVisitor("wonderful_visitor_1")
    ->withContext(["qa_bucketing_integration" => true])
    ->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag('not_exists_flag', 'default_value');

echo "value :" . $flag->getValue() . PHP_EOL;

Flagship::close();
