<?php

namespace Flagship\Utils;

use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{

//    public function testSetTimeout()
//    {
//    }
//
    public function testSetHeaders()
    {
        $client = HttpClient::create();
        $this->assertCount(0, $client->getHeaders());
        $client->setHeaders(['x-sdk-client' => 'PHP']);
        $this->assertCount(1, $client->getHeaders());
        $client->setHeaders(['x-sdk-version' => 'v1']);
        $this->assertCount(2, $client->getHeaders());
    }
//
//    public function testExec()
//    {
//    }

    public function testConstruct()
    {
        $client1 = HttpClient::create();
        $client2 = HttpClient::create();
        $this->assertInstanceOf('Flagship\Utils\HttpClient', $client1);
        $this->assertInstanceOf('Flagship\Utils\HttpClient', $client2);
        $this->assertNotSame($client1, $client2);
    }

//    public function testGet()
//    {
//    }
//
    public function testBuildUrl()
    {
        $client = HttpClient::create();
        $buildMethod = Utils::getMethod($client, 'buildUrl');
        $visitorid = 'visitorId';
        $visitoKey = "visitor";
        $urlOriginal = "https://localhost";
        $urlBuild = $buildMethod->invokeArgs($client, [$urlOriginal,[ $visitoKey => $visitorid]]);
        $this->assertEquals($urlOriginal . '?' . $visitoKey . '=' . $visitorid, $urlBuild);
    }
//
//    public function testCreate()
//    {
//    }
//
//    public function testPost()
//    {
//    }
}
