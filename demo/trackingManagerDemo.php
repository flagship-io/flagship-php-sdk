<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Cache\IHitCacheImplementation;
use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\LogLevel;
use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Enum\EventCategory;
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
    DecisionApiConfig::decisionApi()->setCacheStrategy(2)
    ->setHitCacheImplementation(new HitCacheRedis('127.0.0.1', 6379,0))
    ->setLogLevel(LogLevel::ALL)
);

$now = round(microtime(true) * 1000);

$visitor = Flagship::newVisitor("visitor_2")
    ->withContext(["qa_report" => true, 'is_php' => true])
    ->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag('qa_report_var', 'default_title');

echo "value :" . $flag->getValue() . PHP_EOL;

$visitor->sendHit(new Page("page1"));
$visitor->sendHit(new Page("page2"));
$visitor->sendHit(new Page("page3"));
$visitor->sendHit(new Page("page4"));
$visitor->sendHit(new Page("page5"));

Flagship::close();

$now2 = round(microtime(true) * 1000);

echo "duration:". ($now2 - $now).PHP_EOL;