<?php

namespace Flagship\Utils;

use CurlHandle;
use ErrorException;
use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\HttpResponse;

/**
 * HTTP Client implementation using cURL
 * 
 * Provides HTTP request functionality with automatic error handling,
 * header management, and response parsing.
 */
class HttpClient implements HttpClientInterface
{
    /**
     * cURL handle
     * @var CurlHandle|null
     */
    private ?CurlHandle $curl = null;

    /**
     * cURL options cache
     * @var array<int, mixed>
     */
    private array $options = [];

    /**
     * HTTP headers
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * Construct
     *
     * @throws ErrorException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new ErrorException(FlagshipConstant::CURL_LIBRARY_IS_NOT_LOADED);
        }
    }

    /**
     * Initialize cURL handle with default options
     *
     * @return void
     * @throws ErrorException
     */
    private function curlInit(): void
    {
        $curl = curl_init();

        if (!$curl) {
            throw new ErrorException('Failed to initialize cURL');
        }

        $this->curl = $curl;
        $this->setTimeout();
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLOPT_FILETIME, true);
    }

    /**
     * Set cURL option
     *
     * @param int $option cURL option constant
     * @param mixed $value Option value
     *
     * @return bool Success status
     * @throws ErrorException
     */
    public function setOpt(int $option, mixed $value): bool
    {
        if ($this->curl === null) {
            $this->curlInit();
        }

        /** @var CurlHandle $curl */
        $curl = $this->curl;

        $success = curl_setopt($curl, $option, $value);

        if ($success) {
            $this->options[$option] = $value;
        }

        return $success;
    }

    /**
     * Get all configured cURL options
     *
     * @return array<int, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function setHeaders(array $headers): HttpClientInterface
    {
        foreach ($headers as $key => $value) {
            $key = trim((string)$key);
            $value = trim((string)$value);
            $this->headers[$key] = $value;
        }

        $formattedHeaders = [];
        foreach ($this->headers as $key => $value) {
            $formattedHeaders[] = $key . ': ' . $value;
        }

        $this->setOpt(CURLOPT_HTTPHEADER, $formattedHeaders);

        return $this;
    }

    /**
     * Get all configured headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function setTimeout(float $seconds = FlagshipConstant::REQUEST_TIME_OUT): HttpClientInterface
    {
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $seconds);
        return $this;
    }

    /**
     * Set request URL with optional query parameters
     *
     * @param string $url Base URL
     * @param string|array<string, scalar> $data Query parameters
     * @return HttpClientInterface
     */
    private function setUrl(string $url, string|array $data = ''): HttpClientInterface
    {
        $builtUrl = $this->buildUrl($url, $data);
        $this->setOpt(CURLOPT_URL, $builtUrl);
        return $this;
    }

    /**
     * Execute cURL request and parse response
     *
     * @return HttpResponse Parsed HTTP response
     * @throws Exception On HTTP error or cURL error
     */
    private function exec(): HttpResponse
    {
        if ($this->curl === null) {
            throw new Exception('cURL handle not initialized');
        }

        $rawResponse = curl_exec($this->curl);
        $curlErrorCode = curl_errno($this->curl);
        $curlErrorMessage = curl_error($this->curl);

        /** @var int $httpStatusCode */
        $httpStatusCode = $this->getInfo(CURLINFO_HTTP_CODE);

        $httpError = in_array((int)floor($httpStatusCode / 100), [4, 5], true);

        $httpContentType = $this->getInfo(CURLINFO_CONTENT_TYPE);
        $lastModified = $this->getInfo(CURLINFO_FILETIME);

        $this->closeCurl();

        if ($httpError || $curlErrorCode) {
            $errorData = [
                'curlCode'    => $curlErrorCode,
                'curlMessage' => $curlErrorMessage,
                'httpCode'    => $httpStatusCode,
                'httpMessage' => $rawResponse,
            ];
            $errorMessage = json_encode($errorData) ?: 'Failed to encode error data';
            throw new Exception($errorMessage, $curlErrorCode);
        }

        $response = $rawResponse;
        if ($httpContentType === 'application/json' && is_string($rawResponse)) {
            $parsed = $this->parseResponse($rawResponse);
            if ($parsed !== null) {
                $response = $parsed;
            }
        }

        $responseHeaders = [];
        if (is_int($lastModified) && $lastModified !== -1) {
            $responseHeaders['last-modified'] = date('Y-m-d H:i:s', $lastModified);
        }

        return new HttpResponse($httpStatusCode, $response, $responseHeaders);
    }

    /**
     * Close cURL handle
     * 
     * @return void
     */
    private function closeCurl(): void
    {
        $this->curl = null;
    }

    /**
     * Perform GET request
     *
     * @param string $url Request URL
     * @param array<string, scalar> $params Query parameters
     *
     * @return HttpResponse HTTP response
     * @throws Exception
     */
    public function get(string $url, array $params = []): HttpResponse
    {
        $this->setUrl($url, $params);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        return $this->exec();
    }

    /**
     * Perform POST request
     *
     * @param string $url Request URL
     * @param array<string, scalar> $query Query parameters
     * @param array<string, mixed> $data POST body data
     * @return HttpResponse HTTP response
     * @throws Exception
     */
    public function post(string $url, array $query = [], array $data = []): HttpResponse
    {
        $this->setUrl($url, $query);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, json_encode($data));
        return $this->exec();
    }

    /**
     * Get cURL information
     *
     * @param int|null $opt cURL info option
     *
     * @return mixed cURL info value
     */
    private function getInfo(?int $opt = null): mixed
    {
        if ($this->curl === null) {
            return null;
        }

        return curl_getinfo($this->curl, $opt);
    }

    /**
     * Parse JSON response
     *
     * @param string $rawResponse Raw response string
     * @return mixed Parsed response or null on failure
     */
    private function parseResponse(string $rawResponse): mixed
    {
        return json_decode($rawResponse, true);
    }

    /**
     * Build URL with query parameters
     *
     * @param string $url Base URL
     * @param array<string, string|int|float|bool>|string $data Query parameters
     *
     * @return string Complete URL with query string
     */
    private function buildUrl(string $url, array|string $data = ''): string
    {
        if (empty($data)) {
            return $url;
        }

        $queryMark = str_contains($url, '?') ? '&' : '?';

        $queryString = match (true) {
            is_string($data) => $queryMark . $data,
            default => $queryMark . http_build_query($data, '', '&'),
        };

        return $url . $queryString;
    }

    /**
     * Clean up cURL handle on destruction
     * 
     * PHP 8.0+ automatically closes CurlHandle objects when they go out of scope.
     * This destructor explicitly unsets the handle for clarity.
     */
    public function __destruct()
    {
        $this->closeCurl();
    }
}
