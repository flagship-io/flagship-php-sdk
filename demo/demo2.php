<?php

require_once __DIR__ . '/vendor/autoload.php';

use Flagship\Config\BucketingConfig;
use Flagship\Flagship;

$envId = getenv('FLAGSHIP_ENV_ID');
$apiKey = getenv('FLAGSHIP_API_KEY');
$bucketingDirectory = getenv("FLAGSHIP_BUCKETING_DIRECTORY");

$config = new \Flagship\Config\DecisionApiConfig();
$config = new \Flagship\Config\BucketingConfig();

$onStatusChanged =

$config->setStatusChangedCallable(function ($status) {
    if ($status === \Flagship\Enum\FlagshipStatus::READY) {
        echo "SDK is ready";
    }
});

$config->setBucketingDirectoryPath($bucketingDirectory);

Flagship::start($envId, $apiKey, $config);

$visitor_Id = "visitor_1";
$context = [
    "isPhp" => true
];

$visitor = Flagship::newVisitor($visitor_Id, $context);

while (true) {
    echo "============================================================" . PHP_EOL;
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