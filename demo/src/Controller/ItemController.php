<?php

//start demo
//demo/src/Controller/ItemController.php

declare(strict_types=1);

namespace App\Controller;

use Flagship\Flagship;
use Flagship\Hit\Event;
use Flagship\Enum\EventCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ItemController extends AbstractController
{
    private $visitorId = 'visitorId';
    public function __construct()
    {
    }

    #[Route('/item', name: 'item', methods: ['GET'])]
    public function getItem(Request $request): Response
    {
        $isVip = $request->query->get('isVip') === 'true';

        // Create a new flagship visitor
        $visitor = Flagship::newVisitor($this->visitorId, true)->setContext(['fs_is_vip' => $isVip])->build();

        // Fetch the flags for the visitor
        $visitor->fetchFlags();

        //Get the values of the flags for the visitor
        $fsEnableDiscount = $visitor->getFlag("fs_enable_discount");
        $fsAddToCartBtnColor = $visitor->getFlag("fs_add_to_cart_btn_color");

        $fsEnableDiscountValue = $fsEnableDiscount->getValue(false);
        $fsAddToCartBtnColorValue = $fsAddToCartBtnColor->getValue("blue");

        return $this->json([
            "item" => ["name" => "Flagship T-shirt", "price" => 20],
            "fsEnableDiscount" => $fsEnableDiscountValue,
            "fsAddToCartBtnColor" => $fsAddToCartBtnColorValue
        ]);
    }

    #[Route('/add-to-cart', name: 'add-to-cart', methods: ['POST'])]
    public function addToCart()
    {
        $visitor = Flagship::newVisitor($this->visitorId, true)->setContext([])->build();

        // Send a hit to track an action
        $eventHit = new Event(EventCategory::ACTION_TRACKING, "add-to-cart-clicked");

        $visitor->sendHit($eventHit);

        return $this->json(null);
    }
}
//end demo