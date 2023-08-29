<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Cache\IHitCacheImplementation;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\LogLevel;
use Flagship\Flagship;
use Flagship\Hit\Page;

class HitCacheRedis implements IHitCacheImplementation
{

    private $redis;
    public function __construct($address, $port, $dbIndex)
    {
        $this->redis = new Redis();
        $this->redis->connect($address, $port);
        $this->redis->select($dbIndex);
    }

    /**
     * @inheritDoc
     */
    public function cacheHit(array $hits)
    {
        $redis = $this->redis->multi();
        foreach ($hits as $key => $hit) {
            $redis->set($key, json_encode($hit));
        }
        $redis->exec();
    }

    /**
     * @inheritDoc
     */
    public function lookupHits()
    {
        $keys = $this->redis->keys('*');
        $hits = $this->redis->mGet($keys);
        if (!$hits) {
            return [];
        }
        $hitsOut = [];
        foreach ($hits as $key => $hit) {
            $hitsOut[$keys[$key]] = json_decode($hit, true);
        }
        return $hitsOut;
    }

    /**
     * @inheritDoc
     */
    public function flushHits(array $hitKeys)
    {
        $this->redis->del($hitKeys);
    }

    public function flushAllHits()
    {
        $this->redis->flushDB();
    }
}


$ENV_ID = '';
$API_KEY = '';
$REDIS_HOST = getenv("REDIS_HOST");
$REDIS_PORT = getenv("REDIS_PORT");
$APP_POLLING_HOST = getenv("APP_POLLING_HOST");
$APP_POLLING_PORT = getenv("APP_POLLING_PORT");

Flagship::start(
    $ENV_ID,
    $API_KEY,
    DecisionApiConfig::bucketing("http://$APP_POLLING_HOST:$APP_POLLING_PORT/bucketing")
        ->setCacheStrategy(CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE)
        ->setTimeout(5000)
//        ->setHitCacheImplementation(new HitCacheRedis($REDIS_HOST, $REDIS_PORT, 0))
        ->setLogLevel(LogLevel::ALL)
);

$visitor = Flagship::newVisitor("visitor-A")
//    ->isAuthenticated(true)
    ->withContext(["testing_tracking_manager" => true])
    ->build();

$visitor->fetchFlags();

sleep(10);

$flag = $visitor->getFlag('my_flag', 'default_value');

echo "value :" . $flag->getValue() . PHP_EOL;

$visitor->sendHit(new Page("page1"));

//$visitor2 = Flagship::newVisitor("visitor-B")
//    ->withContext(["testing_tracking_manager" => true])
//    ->build();

//$visitor2->fetchFlags();

//$flag = $visitor2->getFlag('my_flag', 'default_value');

//echo "value :" . $flag->getValue() . PHP_EOL;

//$visitor2->sendHit(new Page("page1"));

//$visitor->setConsent(false);
$visitor->sendHit(new Page("page2"));
sleep(10);
$visitor->setConsent(false);
//$visitor->sendHit(new Page("page3"));
//$visitor->sendHit(new Page("page4"));
//$visitor->sendHit(new Page("page5"));

// Note: A appeler avant que le script termine l'exécution. Par exemple l'event kernel.terminate pour symfony
//sleep(10);
//Flagship::close();
