<?php

/*
 * Modifications
 *{
    "key": "reference",
    "key2": 1,
    "key3": 3,
    "key4": 4
* }

 * Toggle
 * btnColor: “#EE3300” (string)
 * background: “bleu ciel” (string)

 * */

use Flagship\Flagship;

require __dir__ . '/../vendor/autoload.php';

Flagship::start('envId', 'apiKey');
$visitor = Flagship::newVisitor('visitor_5');
if ($visitor) {
    $startTime = microtime(true);
    $visitor->synchronizedModifications();
    $endTime = microtime(true);

    echo "key = background, value = " .  $visitor->getModification('background', 'white') . "\n";
    echo "key = key, value = " . $visitor->getModification('key', 'key') . "\n";

    $visitor->activateModification('background');

    echo 'sync time ' . ($endTime - $startTime) . "\n";
}
