<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\DecisionApiConfig;
use Flagship\Flagship;

$config = new DecisionApiConfig();

Flagship::start("", "", $config);

$visitor = Flagship::newVisitor("visitor-F")->context([
    "qa_getflag" => true
])->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag("qa_flag", 10);

var_dump("value:", $flag->value());
var_dump("value:", $flag->value(false));

var_dump("exist:", $flag->exists());

var_dump(json_encode($flag->getMetadata()));

$flag->userExposed();

$flag = $visitor->getFlag("qa_flag", "default");

var_dump("value 2:", $flag->value());

$flag = $visitor->getFlag("qa_flag", null);

var_dump("value 3:", $flag->value());

var_dump("metadata2:", json_encode($flag->getMetadata()));
