<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Flagship\Config\DecisionApiConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Flagship;
use PHPUnit\Framework\TestCase;

$envId = getenv('FS_ENV_ID');
$apiKey = getenv('FS_API_KEY');

Flagship::start($envId, $apiKey, DecisionApiConfig::decisionApi()
    ->setCacheStrategy(CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE));

$visitor = Flagship::newVisitor("visitor-1", true)
    ->setContext(['ci-test' => true, 'test-ab' => true])
    ->build();

$visitor->fetchFlags();

$defaultValue = 'default-value';
$flag = $visitor->getFlag('ci_flag_1', );
$flagValue = $flag->getValue($defaultValue);

TestCase::assertSame($defaultValue, $flagValue);
TestCase::assertSame('Test-campaign ab', $flag->getMetadata()->getCampaignName());

//Test 2
$visitor = Flagship::newVisitor("visitor-2", true)
    ->setContext(['ci-test' => true, 'test-ab' => true])
    ->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag('ci_flag_1');
$flagValue = $flag->getValue($defaultValue);

TestCase::assertSame("flag-1-value-1", $flagValue);
TestCase::assertSame('Test-campaign ab', $flag->getMetadata()->getCampaignName());

//Test 3
$visitor = Flagship::newVisitor("visitor-6", true)
    ->setContext(['ci-test' => false, 'test-ab' => true])
    ->build();

$visitor->fetchFlags();

$flag = $visitor->getFlag('ci_flag_1');
$flagValue = $flag->getValue($defaultValue);

TestCase::assertSame($defaultValue, $flagValue);
TestCase::assertSame("", $flag->getMetadata()->getCampaignName());

Flagship::close();
