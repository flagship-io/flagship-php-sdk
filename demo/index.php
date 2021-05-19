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

use Flagship\Enum\EventCategory;
use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Hit\Item;
use Flagship\Hit\Transaction;

require __dir__ . '/../vendor/autoload.php';

$config = new \Flagship\FlagshipConfig();
$config->setLogManager(new CustomLogManager())
    ->setTimeout(2);

Flagship::start('envId', 'apiKey');
$total = 6000;
for ($i = 0; $i < $total; $i++) {
    $old = 10 * ($i + 1);
    $visitorId = 'visitor_' . $i . '_' . rand(0, 20);
    $visitor = Flagship::newVisitor($visitorId, ['old' => $old, 'town' => 'london']);

    if ($visitor) {
        $visitor->updateContext();
        $startTime = microtime(true);
        $visitor->synchronizedModifications();
        $endTime = microtime(true);

        echo "key = my_html, value = " . $visitor->getModification('my_html', '<div>default</div>', true) . "\n";
        echo "key = background, value = " . $visitor->getModification('background', 'white', true) . "\n";

        $page = new \Flagship\Hit\Page("http://localhost");
        $loadEvent = new \Flagship\Hit\Event("load", "load");
        $clickEvent = new \Flagship\Hit\Event("click", "click");
        $buyEvent = new \Flagship\Hit\Event('buy', 'buy');
        $transaction = new \Flagship\Hit\Transaction('transaction' . $i, 'affiliation' . round($i / 10));
        $transaction->setShippingMethod('plan')
            ->setTotalRevenue(45 * $i)
            ->setPaymentMethod($i % 2 ? 'visa' : 'mastercard')
            ->setShippingCosts(12 * $i)
            ->setCurrency('USD');

        $transaction = (new Transaction("#12345", "affiliation"))
            ->setCouponCode("code")
            ->setCurrency("EUR")
            ->setItemCount(1)
            ->setPaymentMethod("creditcard")
            ->setShippingCosts(9.99)
            ->setTaxes(19.99)
            ->setTotalRevenue(199.99)
            ->setShippingMethod("1day");

        $item = (new Item("#12345", "product", "sku123"))
            ->setItemCategory("test")
            ->setItemPrice(199.99)
            ->setItemQuantity(1);

          $event = (new Event(EventCategory::USER_ENGAGEMENT, "action"))
              ->setEventLabel("label")
              ->setEventValue(100);

        $ran = rand(1, 20);

        $item = new \Flagship\Hit\Item('transaction' . $i, 'article' . $ran, 'code' . $ran);
        $item->setItemPrice($ran * 2)
            ->setItemQuantity($ran);

        $visitor->sendHit($page);
        $visitor->sendHit($loadEvent);
        $visitor->sendHit($clickEvent);
        $visitor->sendHit($buyEvent);
        $startTimeHit = microtime(true);
        $visitor->sendHit($transaction);
        $endTimeHit = microtime(true);
        $visitor->sendHit($item);

        echo 'sync time ' . ($endTime - $startTime) . "\n";
        echo 'sync time ' . ($endTimeHit - $startTimeHit) . "\n";
    }
}
