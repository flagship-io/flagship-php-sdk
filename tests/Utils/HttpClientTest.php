<?php

namespace Flagship\Utils;

require_once __dir__ . '/../Assets/Curl.php';

use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    public function testSetOption(){
        $client = new HttpClient();
        $optionKey = CURLOPT_TIMEOUT;
        $optionValue = 2000;
        $client->setOpt($optionKey, $optionValue);
        $this->assertSame($client->getOptions()[$optionKey],  $optionValue);
    }

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
        $versionSDkKey = 'sdk';
        $versionSDkValue = '1';
        $urlOriginal = "https://localhost";
        $urlExpected = $urlOriginal . '?' . $visitoKey . '=' . $visitorid . '&' . $versionSDkKey . '=' . $versionSDkValue;
        $urlBuild = $buildMethod->invokeArgs($client, [$urlOriginal,
            [
                $visitoKey => $visitorid,
                $versionSDkKey => $versionSDkValue
            ]
        ]);
        $this->assertEquals($urlExpected, $urlBuild);
        $visitor= 'visitor=visitor';
        $urlExpected= $urlOriginal . '?'. $visitor;
        $urlBuild = $buildMethod->invokeArgs($client, [$urlOriginal, $visitor]);
        $this->assertEquals($urlExpected, $urlBuild);

    }

}
