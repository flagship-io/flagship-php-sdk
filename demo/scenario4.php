<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\DecisionApiConfig;
use Flagship\Flagship;

$config = new DecisionApiConfig();

Flagship::start("", "", $config);

$visitor = Flagship::newVisitor("visitor-A")->context([
    "qa_getflag" => true
])->build();

$visitor->synchronizeModifications();


$value = $visitor->getModification("qa_flag", "default");

var_dump("value:", $value);

var_dump($visitor->getModificationInfo('qa_flag'));

$visitor->activateModification('qa_flag');
