<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\DecisionApiConfig;
use Flagship\Flagship;

$config = new DecisionApiConfig();

Flagship::start("", "", $config);

$visitor = Flagship::newVisitor("visitor-A")->context([
    "qa_getflag" => true
])->build();

$visitor->fetchFlags();


$flag = $visitor->getFlag("wrong", 10);

var_dump("value:", $flag->getValue());

var_dump("exist:", $flag->exists());

var_dump(json_encode($flag->getMetadata()));

$flag->userExposed();
