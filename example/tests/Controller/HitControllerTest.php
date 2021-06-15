<?php

namespace Controller;

use App\Traits\ErrorFormatTrait;
use TestCase;
use App\Http\Controllers\HitController;
use Flagship\Hit\Event;
use Flagship\Hit\Item;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Hit\Transaction;

class HitControllerTest extends TestCase
{
    use GeneralMockTrait;
    use ErrorFormatTrait;

    public function testSendHitPageAndScreen()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $visitor->expects($this->exactly(2))->method('sendHit')
            ->withConsecutive(
                [$this->isInstanceOf(Page::class)],
                [$this->isInstanceOf(Screen::class)]
            );

        $this->post('/hit', [
            "dl" => "Localhost",
            "t" => HitController::PAGE
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode($visitor->getConfig()), $this->response->content());

        $this->post('/hit', [
            "dl" => "screen",
            "t" => HitController::SCREEN
        ]);
        $this->assertJsonStringEqualsJsonString(json_encode($visitor->getConfig()), $this->response->content());
    }

    public function testSendHitEvent()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $visitor->expects($this->once())->method('sendHit')
            ->withConsecutive(
                [$this->isInstanceOf(Event::class)]
            );

        $this->post('/hit', [
            "ec" => "event cate",
            "ea" => 'event action',
            'ev' => 'event value',
            'el' => 'event label',
            "t" => HitController::EVENT
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode($visitor->getConfig()), $this->response->content());

        $this->post('/hit', [
            "t" => HitController::EVENT
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["ea" => ["The ea field is required."],"ec" => ["The ec field is required."]])),
            $this->response->content()
        );
    }

    public function testSendHitTransaction()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $visitor->expects($this->once())->method('sendHit')
            ->withConsecutive(
                [$this->isInstanceOf(Transaction::class)]
            );

        $this->post('/hit', [
            "tid" => "transaction id",
            "ta" => 'transaction affil',
            'icn' => 2,
            'pm' => 'payment method',
            'sm' => 'shipping Method',
            'tc' => "USD",
            'tcc' => 'coupon',
            'tr' => 250,
            'ts' => 100,
            'tt' => 58,
            "t" => HitController::TRANSACTION
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode($visitor->getConfig()), $this->response->content());

        $this->post('/hit', [
            "t" => HitController::TRANSACTION
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["tid"=>["The tid field is required."],"ta"=>["The ta field is required."]])),
            $this->response->content()
        );
    }

    public function testSendHitItem()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $visitor->expects($this->once())->method('sendHit')
            ->withConsecutive(
                [$this->isInstanceOf(Item::class)]
            );

        $this->post('/hit', [
            "tid" => "transaction id",
            "ic" => '5557er',
            "in" => 'item name',
            "ip" => 258,
            "iq" => 4,
            "iv" => "item category",
            "t" => HitController::ITEM
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode($visitor->getConfig()), $this->response->content());

        $this->post('/hit', [
            "t" => HitController::ITEM
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError([
                "tid"=>["The tid field is required."],
                "ic"=>["The ic field is required."],
                "in"=>["The in field is required."]])),
            $this->response->content()
        );
    }
}
