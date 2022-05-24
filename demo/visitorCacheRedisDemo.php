<?php

require_once __DIR__ . '/../vendor/autoload.php';


use Flagship\Cache\IVisitorCacheImplementation;
use Flagship\Config\FlagshipConfig;
use Flagship\Flagship;


$ENV_ID = '';
$API_KEY = '';

/**
 * Implementing visitor caches with redis
 */
class VisitorCacheRedis implements IVisitorCacheImplementation{

    private $redis;
    public function __construct($address, $port)
    {
        $this->redis = new Redis();
        $this->redis->connect($address, $port);
    }

    public function cacheVisitor($visitorId, array $data)
    {
        $this->redis->set($visitorId, json_encode($data, JSON_NUMERIC_CHECK));
    }

    public function lookupVisitor($visitorId)
    {
        $data = $this->redis->get($visitorId);
        if (!$data){
            return null;
        }
        return json_decode($data, true);
    }

    public function flushVisitor($visitorId)
    {
      $this->redis->del($visitorId);
    }
}

Flagship::start($ENV_ID, $API_KEY,
    FlagshipConfig::bucketing("http://127.0.0.1:8080/bucketing")
    ->setTimeout(10000)
    ->setVisitorCacheImplementation(new VisitorCacheRedis("127.0.0.1", 6379))
);

function test1(){
    $visitor = Flagship::newVisitor("visitorID")
        ->withContext(["plan"=>"enterprise"])
        ->build();

    $visitor->fetchFlags();

    echo "############# Flag cache ##################### \n";

    $flagCache = $visitor->getFlag("myAwesomeFeature", 0) ;
    echo "value: ". $flagCache->getValue(false)."\n";
    echo "exists: ". $flagCache->exists()."\n";
    echo "metadata: ". json_encode($flagCache->getMetadata())."\n";

    echo "############# End Flag cache ##################### \n\n";

    echo "############# Flag cache ##################### \n";

    $flagCache = $visitor->getFlag("js-qa-app", "default js-qa-app") ;
    echo "value: ". $flagCache->getValue(false)."\n";
    echo "exists: ". $flagCache->exists()."\n";
    echo "metadata: ". json_encode($flagCache->getMetadata())."\n";

    echo "############# End Flag cache ##################### \n\n";
}

function test2(){
    $visitor = Flagship::newVisitor("visitorID")
        ->withContext(["plan"=>"enterprise"])
        ->build();

    $visitor->fetchFlags();

    echo "############# Flag cache ##################### \n";

    $flagCache = $visitor->getFlag("myAwesomeFeature", 0) ;
    echo "value: ". $flagCache->getValue(false)."\n";
    echo "exists: ". (bool) $flagCache->exists()."\n";
    echo "metadata: ". json_encode($flagCache->getMetadata())."\n";

    echo "############# End Flag cache ##################### \n\n";

    $visitor->setConsent(false);
}

function test3(){
    $visitor = Flagship::newVisitor("visitorID")
        ->withContext(["plan"=>"enterprise"])
        ->build();

    $visitor->fetchFlags();

    echo "############# Flag cache ##################### \n";

    $flagCache = $visitor->getFlag("cache", 0) ;
    echo "value: ". $flagCache->getValue(false)."\n";
    echo "exists: ". (bool) $flagCache->exists()."\n";
    echo "metadata: ". json_encode($flagCache->getMetadata())."\n";

    echo "############# End Flag cache ##################### \n\n";

    echo "############# Flag cache-2 ##################### \n";

    $flagCache = $visitor->getFlag("cache-2", 0) ;
    echo "value: ". $flagCache->getValue(false)."\n";
    echo "exists: ". $flagCache->exists() ."\n";
    echo "metadata: ". json_encode($flagCache->getMetadata())."\n";

    echo "############# End Flag cache-2 ##################### \n\n";
}

test1();
