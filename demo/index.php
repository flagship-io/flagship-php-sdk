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

Flagship::start('c1ndrd07m0300ro0jf20', 'QzdTI1M9iqaIhnJ66a34C5xdzrrvzq6q8XSVOsS6');
$total= 6000;
for ($i = 0; $i < $total; $i++) {
    $old = 10 * ($i + 1);
    $visitorId= 'visitor_' . $i.'_'.rand(0,20);
    $visitor = Flagship::newVisitor($visitorId, ['old' => $old, 'town' => 'london']);

    if ($visitor) {
        $startTime = microtime(true);
        $visitor->synchronizedModifications();
        $endTime = microtime(true);

        echo "key = my_html, value = " . $visitor->getModification('my_html', '<div>default</div>', true) . "\n";
        echo "key = background, value = " . $visitor->getModification('background', 'white', true) . "\n";

        $page = new \Flagship\Hit\Page("http://localhost");
        $loadEvent = new \Flagship\Hit\Event("load", "load");
        $clickEvent = new \Flagship\Hit\Event("click", "click");
        $buyEvent = new \Flagship\Hit\Event('buy', 'buy');
        $transaction = new \Flagship\Hit\Transaction('transaction'.$i, 'affiliation'.round($i/10));
        $transaction->setShippingMethod('plan')
            ->setRevenue(45*$i)
            ->setPaymentMethod($i%2? 'visa':'mastercard')
            ->setShippingCost(12*$i)
            ->setCurrency('USD');

        $ran=rand(1,20);

        $item = new \Flagship\Hit\Item('transaction'.$i, 'article'.$ran, 'code'.$ran);
        $item->setItemPrice($ran*2)
            ->setItemQuantity( $ran);

        $visitor->sendHit($page);
        $visitor->sendHit($loadEvent);
        $visitor->sendHit($clickEvent);
        $visitor->sendHit($buyEvent);
        $startTimeHit = microtime(true);
        $visitor->sendHit($transaction);
        $endTimeHit = microtime(true);
        $visitor->sendHit($item);

        echo 'sync time ' . ($endTime - $startTime) . "\n";
        echo 'sync time ' . ($endTimeHit- $startTimeHit) . "\n";
    }
}

