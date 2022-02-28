<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\EventCategory;
use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;


$config = new DecisionApiConfig();
$config->setTimeout(20000);

Flagship::start("", "", $config);

$visitor = Flagship::newVisitor("alias")->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag("php", "default");

var_dump("value:", $flag->getValue());

var_dump("visitorId :" . $visitor->getVisitorId());
var_dump("anonymousId :" . $visitor->getAnonymousId());

$page = new Page("abtastylab");

$visitor->sendHit($page);

echo 'authenticate';

$visitor->authenticate("alias_01");


var_dump("visitorId :" . $visitor->getVisitorId());
var_dump("anonymousId :" . $visitor->getAnonymousId());

$visitor->fetchFlags();

var_dump("value:", $flag->getValue());

$page = new Page("abtastylab");

$visitor->sendHit($page);

echo 'unauthenticate';

$visitor->unauthenticate();


var_dump("visitorId :" . $visitor->getVisitorId());
var_dump("anonymousId :" . $visitor->getAnonymousId());

$visitor->fetchFlags();

var_dump("value:", $flag->getValue());

$page = new Page("abtastylab");

$visitor->sendHit($page);
