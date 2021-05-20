<?php

namespace Controller;

use TestCase;

class EnvControllerTest extends TestCase
{
    public function testUpdate()
    {
        $data = [
            "environment_id" => "env_id",
            "api_key" => "api_key",
            "timeout" => 2000
        ];
        $this->put('/env', $data);
        $this->assertJsonStringEqualsJsonString(json_encode([ 'data' => $data]), $this->response->getContent());
    }


    public function testIndex()
    {
        $data = [
            "environment_id" => "env_id",
            "api_key" => "api_key",
            "timeout" => 2000
        ];
        $this->put('/env', $data);
        $this->get('/env');
        $this->assertJsonStringEqualsJsonString(json_encode([ 'data' => $data]), $this->response->getContent());
    }
}
