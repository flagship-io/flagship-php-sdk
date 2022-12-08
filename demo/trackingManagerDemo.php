<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Cache\IHitCacheImplementation;
use Flagship\Config\DecisionApiConfig;
use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Enum\EventCategory;

class HitCacheRedis implements IHitCacheImplementation{

    private $redis;
    public function __construct($address, $port)
    {
        $this->redis = new Redis();
        $this->redis->connect($address, $port);
    }

    /**
     * @inheritDoc
     */
    public function cacheHit(array $hits)
    {
        // TODO: Implement cacheHit() method.
    }

    /**
     * @inheritDoc
     */
    public function lookupHits()
    {
        // TODO: Implement lookupHits() method.
    }

    /**
     * @inheritDoc
     */
    public function flushHits(array $hitKeys)
    {
        // TODO: Implement flushHits() method.
    }

    public function flushAllHits()
    {
        // TODO: Implement flushAllHits() method.
    }
}

Flagship::start("ENV_ID", "API_KEY",
    DecisionApiConfig::decisionApi()->setCacheStrategy(3));

$visitor = Flagship::newVisitor("visitor_123")
    ->withContext(["qa_report" => true, 'is_php' => true])
    ->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag('qa_report_var', 'default_title');

echo "value :" . $flag->getValue() . PHP_EOL;

$visitor->sendHit(new Event(EventCategory::USER_ENGAGEMENT, "feature_click"));

Flagship::close();