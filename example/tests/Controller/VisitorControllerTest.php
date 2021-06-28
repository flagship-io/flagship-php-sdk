<?php

namespace Controller;

use App\Traits\ErrorFormatTrait;
use TestCase;

class VisitorControllerTest extends TestCase
{
    use GeneralMockTrait;
    use ErrorFormatTrait;

    public function testIndex()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);
        $array = [
            'visitor_id' => $visitor->getVisitorId(),
            'context' => $visitor->getContext(),
            "hasConsented" => false
        ];
        $this->get('/visitor');

        $this->assertJsonStringEqualsJsonString(json_encode($array), $this->response->content());
    }

    public function testUpdate()
    {
        $this->startFlagShip();
        $visitorId = "visitor_id";
        $context = [
            "age" => 20
        ];


        $this->put('/visitor', [
            'visitor_id' => $visitorId,
            'consent' => false,
            'context' => $context,
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode([]), $this->response->getContent());

        $this->put('/visitor', [
            'context' => $context,
            'consent' => false,
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["visitor_id" => ["The visitor id field is required."]])),
            $this->response->getContent()
        );


        $this->put('/visitor', [
            'visitor_id' => $visitorId,
            'context' => $context,
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["consent" => ["The consent field is required."]])),
            $this->response->getContent()
        );
    }

    public function testUpdateContext()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $this->put('/visitor/context/key', ['type' => 'string', 'value' => 'valueString']);

        $this->assertJsonStringEqualsJsonString(json_encode($visitor), $this->response->getContent());

        $this->put('/visitor/context/key', []);

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError([
                "type" => ["The type field is required."],
                "value" => ["The value field is required."]])),
            $this->response->getContent()
        );

        //Test type check
        $this->put('/visitor/context/key', ['type' => 'double', 'value' => 'valueString']);
        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["value" => ["The value is not double"]])),
            $this->response->getContent()
        );
    }

    public function testUpdateConsent()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);

        $this->put('/visitor/consent', ['value' => false]);

        $this->assertFalse($visitor->hasConsented());

        $this->put('/visitor/consent', ['value' => true]);

        $this->assertTrue($visitor->hasConsented());

        $this->assertJsonStringEqualsJsonString(json_encode($visitor), $this->response->getContent());

        $this->put('/visitor/consent', []);

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["value" => ["The value field is required."]])),
            $this->response->getContent()
        );

        //Test type check
        $this->put('/visitor/consent', ['value' => 'valueString']);
        $this->assertJsonStringEqualsJsonString(
            json_encode($this->formatError(["value" => ["The value is not bool"]])),
            $this->response->getContent()
        );
    }
}
