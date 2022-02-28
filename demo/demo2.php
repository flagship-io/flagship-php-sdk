<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Flagship;



$ENV_ID = '';
$API_KEY = '';



Flagship::start($ENV_ID, $API_KEY, \Flagship\Config\FlagshipConfig::decisionApi()->setTimeout(10000));

for ($i = 1; $i <= 3; $i++) {
    $visitor = Flagship::newVisitor("300122-php-trans-" . $i)->build();
    $visitor->fetchFlags();
    $flag = $visitor->getFlag("php", "test");

    echo $flag->getValue();

//    echo $visitor->getModification("php", "test", true);

    $page = new \Flagship\Hit\Page("https://www.sdk.com/abtastylab/php/310122-" . $i);
    $visitor->sendHit($page);

    $screen = new \Flagship\Hit\Screen("abtastylab-php-" . $i);

    $visitor->sendHit($screen);

    $transaction = new \Flagship\Hit\Transaction($visitor->getVisitorId(), "KPI1");

    $visitor->sendHit($transaction);

    $event = new \Flagship\Hit\Event(\Flagship\Enum\EventCategory::USER_ENGAGEMENT, "KP2");
    $event->setValue(10);

    $visitor->sendHit($event);
}
