<?php

namespace Flagship\Utils;

use Exception;
use ErrorException;
use phpmock\phpunit\PHPMock;
use Flagship\Model\HttpResponse;
use PHPUnit\Framework\TestCase;
use Flagship\Enum\FlagshipConstant;

class HttpClientTest extends TestCase
{
    use PHPMock;

    private HttpClient $client;

    protected function setUp(): void {}

    public function testConstructorThrowsExceptionWhenCurlNotLoaded(): void
    {
        $extensionLoaded = $this->getFunctionMock('Flagship\Utils', 'extension_loaded');
        $extensionLoaded->expects($this->once())
            ->with('curl')
            ->willReturn(false);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage(FlagshipConstant::CURL_LIBRARY_IS_NOT_LOADED);

        new HttpClient();
    }

    public function testConstructorSucceedsWhenCurlIsLoaded(): void
    {
        $extensionLoaded = $this->getFunctionMock('Flagship\Utils', 'extension_loaded');
        $extensionLoaded->expects($this->once())
            ->with('curl')
            ->willReturn(true);

        $client = new HttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testCurlInit(): void
    {
        $this->mockExtensionLoaded();

        // Only mock curl_init, let it create real handle
        $this->client = new HttpClient();

        $curlInit = $this->getFunctionMock('Flagship\Utils', 'curl_init');

        $curlInit->expects($this->any())
            ->willReturnCallback(function () {
                return \curl_init();
            });

        // Use reflection to access private method
        $reflection = new \ReflectionClass(HttpClient::class);
        $method = $reflection->getMethod('curlInit');
        $method->setAccessible(true);

        // Invoke the private method
        $method->invoke($this->client);

        // If no exception is thrown, the test passes
        $this->assertTrue(true);
    }

    public function testCurlInitThrowsExceptionOnFailure(): void
    {
        $this->mockExtensionLoaded();

        $curlInit = $this->getFunctionMock('Flagship\Utils', 'curl_init');
        $curlInit->expects($this->any())
            ->willReturn(false);

        $this->client = new HttpClient();

        $reflection = new \ReflectionClass(HttpClient::class);
        $method = $reflection->getMethod('curlInit');
        $method->setAccessible(true);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Failed to initialize cURL');

        $method->invoke($this->client);
    }

    // setOpt() tests
    public function testSetOptInitializesCurlAndSetsOption(): void
    {
        $this->mockExtensionLoaded();

        $curlSetopt = $this->getFunctionMock('Flagship\Utils', 'curl_setopt');
        $curlSetopt->expects($this->any())
            ->willReturn(true);

        $this->client = new HttpClient();
        $result = $this->client->setOpt(CURLOPT_VERBOSE, true);

        $this->assertTrue($result);
        $options = $this->client->getOptions();
        $this->assertArrayHasKey(CURLOPT_VERBOSE, $options);
    }

    public function testSetOptReturnsTrueOnSuccess(): void
    {
        $this->setupClientWithMockedCurl();

        $result = $this->client->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $this->assertTrue($result);
    }

    public function testSetOptStoresMultipleOptions(): void
    {
        $this->setupClientWithMockedCurl();

        $this->client->setOpt(CURLOPT_VERBOSE, true);
        $this->client->setOpt(CURLOPT_MAXREDIRS, 10);
        $this->client->setOpt(CURLOPT_FOLLOWLOCATION, false);

        $options = $this->client->getOptions();
        $this->assertTrue($options[CURLOPT_VERBOSE]);
        $this->assertEquals(10, $options[CURLOPT_MAXREDIRS]);
        $this->assertFalse($options[CURLOPT_FOLLOWLOCATION]);
    }

    // getOptions() tests
    public function testGetOptionsReturnsEmptyArrayInitially(): void
    {
        $this->mockExtensionLoaded();
        $client = new HttpClient();

        $options = $client->getOptions();
        $this->assertIsArray($options);
        $this->assertEmpty($options);
    }

    // setHeaders() tests
    public function testSetHeadersStoresSingleHeader(): void
    {
        $this->mockExtensionLoaded();
        $this->client = new HttpClient();

        $this->client->setHeaders(['Content-Type' => 'application/json']);

        $headers = $this->client->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    public function testSetHeadersStoresMultipleHeaders(): void
    {
        $this->mockExtensionLoaded();
        $this->client = new HttpClient();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer token123',
            'X-Custom-Header' => 'custom-value'
        ];

        $this->client->setHeaders($headers);

        $storedHeaders = $this->client->getHeaders();
        $this->assertCount(3, $storedHeaders);
        $this->assertEquals('application/json', $storedHeaders['Content-Type']);
        $this->assertEquals('Bearer token123', $storedHeaders['Authorization']);
    }


    public function testSetHeadersMergesWithExistingHeaders(): void
    {
        $this->mockExtensionLoaded();
        $this->client = new HttpClient();

        $this->client->setHeaders(['Content-Type' => 'application/json']);
        $this->client->setHeaders(['Authorization' => 'Bearer token']);

        $headers = $this->client->getHeaders();
        $this->assertCount(2, $headers);
    }

    public function testSetHeadersOverwritesExistingHeader(): void
    {
        $this->mockExtensionLoaded();
        $this->client = new HttpClient();

        $this->client->setHeaders(['Content-Type' => 'application/json']);
        $this->client->setHeaders(['Content-Type' => 'text/html']);

        $headers = $this->client->getHeaders();
        $this->assertEquals('text/html', $headers['Content-Type']);
    }

    // setTimeout() tests
    public function testSetTimeoutWithDefaultValue(): void
    {
        $this->setupClientWithMockedCurl();

        $result = $this->client->setTimeout();

        $options = $this->client->getOptions();
        $this->assertEquals(FlagshipConstant::REQUEST_TIME_OUT, $options[CURLOPT_TIMEOUT]);
        $this->assertEquals(FlagshipConstant::REQUEST_TIME_OUT, $options[CURLOPT_CONNECTTIMEOUT]);
        $this->assertSame($this->client, $result);
    }

    public function testSetTimeoutWithCustomValue(): void
    {
        $this->setupClientWithMockedCurl();

        $this->client->setTimeout(30.5);

        $options = $this->client->getOptions();
        $this->assertEquals(30.5, $options[CURLOPT_TIMEOUT]);
        $this->assertEquals(30.5, $options[CURLOPT_CONNECTTIMEOUT]);
    }


    // get() tests
    public function testGetMakesSuccessfulRequest(): void
    {
        $this->setupClientWithMockedCurl();

        $responseBody = json_encode(['data' => 'test']);

        $curlExec = $this->getFunctionMock('Flagship\Utils', 'curl_exec');
        $curlExec->expects($this->once())
            ->willReturn($responseBody);

        $lastModified = time();
        $curlGetinfo = $this->getFunctionMock('Flagship\Utils', 'curl_getinfo');
        $curlGetinfo->expects($this->any())
            ->willReturnCallback(function ($handle, $opt = null) use ($lastModified) {

                return match ($opt) {
                    CURLINFO_EFFECTIVE_URL => 'https://example.com/api',
                    CURLINFO_HTTP_CODE => 200,
                    CURLINFO_HEADER_SIZE => 0,
                    CURLINFO_CONTENT_TYPE => 'application/json',
                    CURLINFO_FILETIME => $lastModified,
                    default => null,
                };
            });


        $response = $this->client->get('https://example.com/api', ['key' => 'value']);

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('last-modified', $response->getHeaders());
        $this->assertEquals(date('Y-m-d H:i:s', $lastModified), $response->getHeaders()['last-modified']);
        $this->assertEquals(['data' => 'test'], $response->getBody());
    }

    public function testGetWithParameters(): void
    {
        $this->setupClientWithMockedCurl();

        $curlExec = $this->getFunctionMock('Flagship\Utils', 'curl_exec');
        $curlExec->expects($this->once())
            ->willReturn("HTTP/1.1 200 OK\r\n\r\n{}");

        $curlGetinfo = $this->getFunctionMock('Flagship\Utils', 'curl_getinfo');
        $curlGetinfo->expects($this->any())
            ->willReturnCallback(function ($handle, $opt = null) {
                if ($opt === CURLINFO_HTTP_CODE) return 200;
                if ($opt === CURLINFO_HEADER_SIZE) return 19;
                return null;
            });


        $response = $this->client->get('https://example.com', ['param1' => 'value1', 'param2' => 'value2']);

        $options = $this->client->getOptions();
        $this->assertStringContainsString('param1=value1', $options[CURLOPT_URL]);
        $this->assertStringContainsString('param2=value2', $options[CURLOPT_URL]);
    }

    public function testGetThrowsExceptionOnError(): void
    {
        $this->setupClientWithMockedCurl();

        $curlExec = $this->getFunctionMock('Flagship\Utils', 'curl_exec');
        $curlExec->expects($this->once())
            ->willReturn("HTTP/1.1 500 Internal Server Error\r\n\r\n{\"error\":\"Server error\"}");

        $curlGetinfo = $this->getFunctionMock('Flagship\Utils', 'curl_getinfo');
        $curlGetinfo->expects($this->any())
            ->willReturnCallback(function ($handle, $opt = null) {
                if ($opt === CURLINFO_HTTP_CODE) return 500;
                if ($opt === CURLINFO_HEADER_SIZE) return 36;
                return null;
            });


        $this->expectException(Exception::class);

        $this->client->get('https://example.com/error');
    }

    // post() tests
    public function testPostMakesSuccessfulRequest(): void
    {
        $this->setupClientWithMockedCurl();

        $responseBody = json_encode(['status' => 'created']);
        $rawResponse = "HTTP/1.1 201 Created\r\nContent-Type: application/json\r\n\r\n" . $responseBody;

        $curlExec = $this->getFunctionMock('Flagship\Utils', 'curl_exec');
        $curlExec->expects($this->once())
            ->willReturn($rawResponse);

        $curlGetinfo = $this->getFunctionMock('Flagship\Utils', 'curl_getinfo');
        $curlGetinfo->expects($this->any())
            ->willReturnCallback(function ($handle, $opt = null) {
                if ($opt === CURLINFO_HTTP_CODE) return 201;
                if ($opt === CURLINFO_HEADER_SIZE) return 47;
                return null;
            });

        $response = $this->client->post('https://example.com/api', [], ['name' => 'test']);

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostWithQueryAndData(): void
    {
        $this->setupClientWithMockedCurl();

        $curlExec = $this->getFunctionMock('Flagship\Utils', 'curl_exec');
        $curlExec->expects($this->once())
            ->willReturn("HTTP/1.1 200 OK\r\n\r\n{}");

        $curlGetinfo = $this->getFunctionMock('Flagship\Utils', 'curl_getinfo');
        $curlGetinfo->expects($this->any())
            ->willReturnCallback(function ($handle, $opt = null) {
                if ($opt === CURLINFO_HTTP_CODE) return 200;
                if ($opt === CURLINFO_HEADER_SIZE) return 19;
                return null;
            });


        $this->client->post(
            'https://example.com/api',
            ['query' => 'param'],
            ['data' => 'value']
        );

        $options = $this->client->getOptions();
        $this->assertTrue($options[CURLOPT_POST]);
        $this->assertEquals('POST', $options[CURLOPT_CUSTOMREQUEST]);
    }

    // Method chaining tests
    public function testMethodChaining(): void
    {
        $this->setupClientWithMockedCurl();

        $result = $this->client
            ->setTimeout(15)
            ->setHeaders(['X-Test' => 'value'])
            ->setHeaders(['Authorization' => 'Bearer token']);

        $this->assertSame($this->client, $result);

        $options = $this->client->getOptions();
        $headers = $this->client->getHeaders();

        $this->assertEquals(15, $options[CURLOPT_TIMEOUT]);
        $this->assertArrayHasKey('X-Test', $headers);
        $this->assertArrayHasKey('Authorization', $headers);
    }

    // Helper methods
    private function mockExtensionLoaded(): void
    {
        $extensionLoaded = $this->getFunctionMock('Flagship\Utils', 'extension_loaded');
        $extensionLoaded->expects($this->any())
            ->with('curl')
            ->willReturn(true);
    }

    private function setupClientWithMockedCurl(): void
    {
        $this->mockExtensionLoaded();

        $curlSetopt = $this->getFunctionMock('Flagship\Utils', 'curl_setopt');
        $curlSetopt->expects($this->any())
            ->willReturn(true);

        $this->client = new HttpClient();
    }
}
