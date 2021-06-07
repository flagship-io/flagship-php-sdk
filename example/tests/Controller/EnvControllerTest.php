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
            "timeout" => 2000,
            "bucketing" => false,
            "polling_interval" => 0
        ];
        $this->put('/env', $data);
        $this->assertJsonStringEqualsJsonString(json_encode($data), $this->response->getContent());

        $data = [
            "environment_id" => "env_id",
            "api_key" => "api_key",
            "timeout" => 2000,
            "bucketing" => true,
            "polling_interval" => 2000
        ];
        $this->put('/env', $data);
        $this->assertJsonStringEqualsJsonString(json_encode($data), $this->response->getContent());

        //Test validation
        $data = [
            "environment_id" => "env_id",
            "api_key" => "api_key",
            "timeout" => 2000,
            "bucketing" => true,
            "polling_interval" => null
        ];
        $this->put('/env', $data);
        $this->assertJsonStringEqualsJsonString('{"error":{"polling_interval":["The polling interval must be a number."]}}', $this->response->getContent());
    }


    public function testIndex()
    {
        $data = [
            "environment_id" => "env_id",
            "api_key" => "api_key",
            "timeout" => 2000,
            "bucketing" => false,
            "polling_interval" => 0
        ];
        $this->put('/env', $data);
        $this->get('/env');
        $this->assertJsonStringEqualsJsonString(json_encode($data), $this->response->getContent());
    }
}
