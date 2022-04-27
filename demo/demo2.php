<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\EventCategory;
use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Hit\Transaction;


$ENV_ID = '';
$API_KEY = '';



Flagship::start($ENV_ID, $API_KEY, FlagshipConfig::bucketing("http://127.0.0.1:8080/bucketing")
    ->setTimeout(2000));

$startDate = new DateTime();

for ($i = 1; $i <= 1; $i++) {
    $visitor = Flagship::newVisitor("30012-php-trans-" . $i)->build();
    $visitor->fetchFlags();
    $flag = $visitor->getFlag("php", "test");

    echo "time 1: ". (new DateTime())->diff($startDate)->f*1000 . "\n";

    echo $flag->getValue(true)."\n";

//    echo $visitor->getModification("php", "test", true);

    /*$page = new Page("https://www.sdk.com/abtastylab/php/310122-" . $i);
    $visitor->sendHit($page);

    $screen = new Screen("abtastylab-php-" . $i);

    $visitor->sendHit($screen);

    $transaction = new Transaction($visitor->getVisitorId(), "KPI1");

    $visitor->sendHit($transaction);

    $event = new Event(EventCategory::USER_ENGAGEMENT, "KP2");
    $event->setValue(10);

    $visitor->sendHit($event);*/
}

$endDate = new DateTime();

echo "time 2: ". (new DateTime())->diff($startDate)->f*1000 ."\n";