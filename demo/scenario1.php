<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\DecisionApiConfig;
use Flagship\Config\FlagshipConfig;
use Flagship\Flagship;

$config = FlagshipConfig::decisionApi();

Flagship::start("bk87t3jggr10c6l6sdog", "N1Rm3DsCBrahhnGTzEnha31IN4DK8tXl28IykcCX", $config);

$visitor = Flagship::newVisitor("visitor-A")->withContext([
    "qa_getflag" => true
])->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag("qa_flag", "default");

$start = new DateTime();

var_dump("value:", $flag->getValue());

//var_dump("exist:", $flag->exists());

//var_dump(json_encode($flag->getMetadata()));

//$flag->userExposed();

$end = new DateTime();

echo $start->diff($end)->f;
