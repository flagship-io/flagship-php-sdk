<?php

namespace Flagship\Model;

use PHPUnit\Framework\TestCase;

class HttpResponseTest extends TestCase
{

    public function testConstruct()
    {
        $statusCode = 200;
        $body = 'Body';
        $headers = [
            "accept" => "application/json"
        ];
        $httpResponse = new HttpResponse($statusCode, $body, $headers);

        $this->assertSame($statusCode, $httpResponse->getStatusCode());
        $this->assertSame($body, $httpResponse->getBody());

        $body = ['key' => 'body'];
        $statusCode = 204;
        $instance1 = $httpResponse->setBody($body);
        $instance2 = $httpResponse->setStatusCode($statusCode);

        $this->assertSame($statusCode, $httpResponse->getStatusCode());
        $this->assertSame($body, $httpResponse->getBody());
        $this->assertSame($instance1, $instance2);
        $this->assertSame($headers, $httpResponse->getHeaders());
    }
}
