<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Enum\EventCategory;
use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Hit\Screen;


$config = new \Flagship\Config\DecisionApiConfig();
$config->setTimeout(20000);

Flagship::start("", "", $config);

function scenario1()
{
    $visitor = Flagship::newVisitor("visitor_a")
        ->withContext(["qa_report" => true, "is_php" => true])
        ->build();

    $visitor->fetchFlags();

    echo "value 1:" . $visitor->getFlag('qa_report_var', 'test')->getValue() . PHP_EOL;

    sleep(1);

    $visitor->sendHit(new Screen("I LOVE QA"));

    sleep(1);

    $visitor->sendHit(new Event(EventCategory::ACTION_TRACKING, "KP2"));
}

function scenario2()
{
    $visitor = Flagship::newVisitor("zZz_visitor_zZz")->withContext(["qa_report" => true, "is_php" => true])->build();

    $visitor->fetchFlags();

    echo "value 2:" . $visitor->getFlag('qa_report_var', 'test')->getValue() . PHP_EOL;

    sleep(1);

    $visitor->sendHit(new Screen("I LOVE QA"));

    sleep(1);

    $visitor->sendHit(new Event(EventCategory::ACTION_TRACKING, "KP2"));
}

function scenario3()
{
    $visitor = Flagship::newVisitor("visitor_0_0")->withContext(["qa_report" => true, "is_php" => true])->build();

    $visitor->fetchFlags();

    echo "value 3:" . $visitor->getFlag('qa_report_var', 'test')->getValue(false) . PHP_EOL;

    $visitor->sendHit(new Screen("I LOVE QA"));

    sleep(1);

    $visitor->sendHit(new Event(EventCategory::ACTION_TRACKING, "KP2"));
}

function scenario4()
{
    $visitor = Flagship::newVisitor("visitor_B_B")->withContext(["qa_report" => true, "is_php" => true])->build();

    $visitor->fetchFlags();

    echo "value 4:" . $visitor->getFlag('qa_report_var', 'test')->getValue() . PHP_EOL;


//    $visitor->sendHit(new Screen("I LOVE QA"));

//    $visitor->sendHit(new Event(EventCategory::ACTION_TRACKING, "KP2"));
}

function scenario5()
{
    $visitor = Flagship::newVisitor("visitor_1111")->withContext(["qa_report" => true, "is_php" => true])->build();

    $visitor->fetchFlags();

    echo "value 5:" . $visitor->getFlag('qa_report_var', 'test')->getValue() . PHP_EOL;

    sleep(1);
    $visitor->setConsent(false);

    $visitor->sendHit(new Screen("I LOVE QA"));

    sleep(1);

    $visitor->sendHit(new Event(EventCategory::ACTION_TRACKING, "KP2"));
}

function scenario6()
{
    $visitor = Flagship::newVisitor("visitor_22")->withContext(["qa_report" => false, "is_php" => true])->build();

    $visitor->fetchFlags();

    echo "value 6:" . $visitor->getFlag('qa_report_var', 'test')->getValue() . PHP_EOL;

    sleep(1);

    $visitor->sendHit(new Screen("I LOVE QA"));

    sleep(1);

    $visitor->sendHit(new Event(EventCategory::ACTION_TRACKING, "KP2"));
}

function scenario7()
{
    $visitor = Flagship::newVisitor("zZz_visitor_zZz")->withContext(["qa_report" => true, "is_php" => true])->build();

    $visitor->fetchFlags();

    echo "value 7:" . $visitor->getFlag('qa_report_var', 'test')->getValue() . PHP_EOL;

    $visitor->sendHit(new Screen("I LOVE QA"));

    $visitor->sendHit(new Event(EventCategory::ACTION_TRACKING, "KP2"));
}

scenario1();
scenario2();
scenario3();
scenario4();
scenario5();
scenario6();

//scenario7();
