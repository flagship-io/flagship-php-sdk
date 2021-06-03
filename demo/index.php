<?php

require_once __DIR__ . '/vendor/autoload.php';

use Flagship\Enum\DecisionMode;
use Flagship\Flagship;
use Flagship\FlagshipConfig;

$envId  =  getenv('FLAGSHIP_ENV_ID');
$apiKey = getenv('FLAGSHIP_API_KEY');
$config = new FlagshipConfig();
$config->setDecisionMode(DecisionMode::BUCKETING);

Flagship::start($envId, $apiKey, $config);

$visitor_Id = "visitor_1";
$context = [
    "isPhp" => true
];

$visitor = Flagship::newVisitor($visitor_Id, $context);

while (true) {
    echo "============================================================" .  PHP_EOL;
    echo 'visitor context';
    echo json_encode($context);
    echo PHP_EOL;
    if (!$visitor) {
        break;
    }
    $visitor->synchronizedModifications();
     print_r($visitor->getModifications());
    sleep(10);
}
