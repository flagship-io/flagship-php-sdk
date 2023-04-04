<?php

namespace Controller;

use App\Traits\ErrorFormatTrait;
use TestCase;

class AuthControllerTest extends TestCase
{
    use GeneralMockTrait;
    use ErrorFormatTrait;

    public function testAuthenticate()
    {
        $data = $this->startFlagShip();
        $visitorAnonymousId = "visitor_anonymous_id";
        $visitorLoggedId = "visitor_logged_id";
        $this->getVisitorMock($data['environment_id'], $data['api_key'], $visitorAnonymousId);

        $this->put('/authenticate', ['new_visitor_id' => $visitorLoggedId]);

        $expectOutput = [
            "visitorId" => $visitorLoggedId,
            "anonymousId" => $visitorAnonymousId
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expectOutput), $this->response->content());

        $this->put('/authenticate', []);
        $this->assertJsonStringEqualsJsonString(json_encode($this->formatError(
            ["new_visitor_id" => ["The new visitor id field is required."]]
        )), $this->response->content());

        $this->assertResponseStatus(422);
    }

    public function testUnauthenticate()
    {
        $data = $this->startFlagShip();
        $visitorAnonymousId = "visitor_anonymous_id";
        $this->getVisitorMock($data['environment_id'], $data['api_key'], $visitorAnonymousId);

        $this->put('/unauthenticate');

        $expectOutput = [
            "visitorId" => $visitorAnonymousId,
            "anonymousId" => null
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expectOutput), $this->response->content());
    }
}
