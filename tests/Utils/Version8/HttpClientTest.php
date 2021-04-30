<?php

namespace Flagship\Utils\Version8;

require_once __dir__ . '/../../Assets/Curl.php';

use Flagship\Assets\Curl;
use Flagship\Enum\FlagshipConstant;
use Flagship\Utils\HttpClient;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    public function testConstructorFailed(){
        Curl::$extension = false;
        $this->expectException('Exception', FlagshipConstant::CURL_LIBRARY_IS_NOT_LOADED);
        $client = new HttpClient();
    }

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

    public function testPost(){
        $client = new HttpClient();
        $url='http://localhost';
        Curl::$response='"Test-response"';
        Curl::$curlErrorCode=0;
        Curl::$curlHttpCodeInfo=204;

        $response = $client->post($url, [],[]);

        $this->assertInstanceOf('Flagship\Model\HttpResponse',$response);
        $this->assertSame(json_decode(Curl::$response), $response->getBody());
        $this->assertSame(Curl::$curlHttpCodeInfo, $response->getStatusCode());
    }

    public function testPostFailed(){
        $client = new HttpClient();
        $url='http://localhost';
        Curl::$response='{"message": "Forbidden"}';
        Curl::$curlErrorCode=0;
        Curl::$curlHttpCodeInfo=403;
        $this->expectException('Exception');
        $response = $client->post($url, [],[]);
    }
    public function testGet(){
        $client = new HttpClient();
        $url='http://localhost';
        Curl::$response='"Test-response"';
        Curl::$curlErrorCode=0;
        Curl::$curlHttpCodeInfo=204;

        $response = $client->get($url, []);
        $this->assertInstanceOf('Flagship\Model\HttpResponse',$response);
        $this->assertSame(json_decode(Curl::$response), $response->getBody());
        $this->assertSame(Curl::$curlHttpCodeInfo, $response->getStatusCode());
    }
}
