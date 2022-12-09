<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Cache\IHitCacheImplementation;
use Flagship\Config\DecisionApiConfig;
use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Enum\EventCategory;

class HitCacheRedis implements IHitCacheImplementation{

    private $redis;
    const REDIS_PREFIX = 'FLAGSHIP_';
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
        $this->redis->multi();
        foreach ($hits as $key=>$hit) {
            $this->redis->set($key, json_encode($hit));
        }
        $this->redis->exec();
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
    DecisionApiConfig::decisionApi()->setCacheStrategy(1)
    ->setHitCacheImplementation(new HitCacheRedis('127.0.0.1', 6379,0))
    ->setLogLevel(\Flagship\Enum\LogLevel::INFO)
);

$visitor = Flagship::newVisitor("visitor_2")
    ->withContext(["qa_report" => true, 'is_php' => true])
    ->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag('qa_report_var', 'default_title');

echo "value :" . $flag->getValue() . PHP_EOL;

$visitor->sendHit(new Event(EventCategory::USER_ENGAGEMENT, "feature_click"));

Flagship::close();