<?php

namespace Flagship\Utils;

use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{

    public function testSetHeaders()
    {
        $client = new HttpClient();
        $this->assertCount(0, $client->getHeaders());
        $client->setHeaders(['x-sdk-client' => 'PHP']);
        $this->assertCount(1, $client->getHeaders());
        $client->setHeaders(['x-sdk-version' => 'v1']);
        $this->assertCount(2, $client->getHeaders());
    }

    public function testBuildUrl()
    {
        $client = new HttpClient();
        $buildMethod = Utils::getMethod($client, 'buildUrl');
        $visitorid = 'visitorId';
        $visitoKey = "visitor";
        $urlOriginal = "https://localhost";
        $urlBuild = $buildMethod->invokeArgs($client, [$urlOriginal,[ $visitoKey => $visitorid]]);
        $this->assertEquals($urlOriginal . '?' . $visitoKey . '=' . $visitorid, $urlBuild);
    }

}
