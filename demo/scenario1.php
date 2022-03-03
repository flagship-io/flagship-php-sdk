<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\DecisionApiConfig;
use Flagship\Config\FlagshipConfig;
use Flagship\Flagship;

$config = FlagshipConfig::decisionApi();

Flagship::start("", "", $config);

$visitor = Flagship::newVisitor("visitor-A")->withContext([
    "qa_report" => true,
    "is_php" => true
])->build();

$visitor->fetchFlags();

var_dump($visitor->getFlagsDTO());

$flag = $visitor->getFlag("qa_report_var", "default");

$start = new DateTime();

var_dump("value:", $flag->getValue());

var_dump("exist:", $flag->exists());

//var_dump(json_encode($flag->getMetadata()));

$flag->userExposed();
