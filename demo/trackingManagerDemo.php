<?php


require_once __DIR__ . '/vendor/autoload.php';

use Flagship\Cache\IHitCacheImplementation;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Enum\LogLevel;
use Flagship\Flagship;
use Flagship\Hit\Page;

class HitCacheRedis implements IHitCacheImplementation{

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
        foreach ($hits as $key=>$hit) {
            $redis->set($key, json_encode($hit));
        }
        $redis->exec();
    }

    /**
     * @inheritDoc
     */
    public function lookupHits()
    {
        $keys = $this->redis->keys( '*');
        $hits = $this->redis->mGet($keys);
        if (!$hits){
            return [];
        }
        $hitsOut = [];
        foreach ($hits as $key=> $hit) {
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

Flagship::start($ENV_ID, $API_KEY,
    DecisionApiConfig::decisionApi() // DecisionApiConfig::bucketing("http://127.0.0.1:8080/bucketing")
    ->setCacheStrategy(CacheStrategy::PERIODIC_CACHING)
        //->setHitCacheImplementation(new HitCacheRedis('127.0.0.1', 6379,0))
    ->setLogLevel(LogLevel::ALL)
);

$visitor = Flagship::newVisitor("visitor_ID")
    ->withContext(["qa_report" => true, 'is_php' => true])
    ->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag('my_flag_key', 'default_value');

echo "value :" . $flag->getValue() . PHP_EOL;

$visitor->sendHit(new Page("page1"));
$visitor->sendHit(new Page("page2"));
$visitor->sendHit(new Page("page3"));
$visitor->sendHit(new Page("page4"));
$visitor->sendHit(new Page("page5"));

// Note: A appeler avant que le script termine l'exécution. Par exemple l'event kernel.terminate pour symfony
Flagship::close();

