<?php

namespace Controller;

use TestCase;

class VisitorControllerTest extends TestCase
{
    use GeneralMockTrait;

    public function testIndex()
    {
        $data = $this->startFlagShip();
        $visitor = $this->getVisitorMock($data['environment_id'], $data['api_key']);
        $array = [
            'visitor_id' => $visitor->getVisitorId(),
            'context' => $visitor->getContext(),
        ];
        $this->get('/visitor');

        $this->assertJsonStringEqualsJsonString(json_encode($array), $this->response->content());
    }

    public function testUpdate()
    {
        $data = $this->startFlagShip();
        $visitorId = "visitor_id";
        $context = [
            "age" => 20
        ];

        $this->put('/visitor', [
            'visitor_id' => $visitorId,
            'context' => $context,
        ]);

        $this->assertJsonStringEqualsJsonString(json_encode([]), $this->response->getContent());

        $this->put('/visitor', [
            'context' => $context,
        ]);

        $this->assertJsonStringEqualsJsonString(
            '{"error":{"visitor_id":["The visitor id field is required."]}}',
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
            '{"error":{"type":["The type field is required."], "value": ["The value field is required."]}}',
            $this->response->getContent()
        );

        //Test type check
        $this->put('/visitor/context/key', ['type' => 'double', 'value' => 'valueString']);
        $this->assertJsonStringEqualsJsonString(
            '{"error":{"value":["The value is not double"]}}',
            $this->response->getContent()
        );
    }
}
