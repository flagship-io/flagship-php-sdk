<?php

namespace Flagship\Utils;

use ErrorException;
use Exception;
use Flagship\Enum\FlagshipConstant;

class HttpClient implements HttpClientInterface
{

    private $curl;

    private $options;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * Construct
     *
     * @throws ErrorException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new ErrorException('cURL library is not loaded');
        }
        $this->curl = curl_init();

        $this->setTimeout();
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Set Opt
     *
     * @param $option
     * @param $value
     *
     * @return boolean
     */
    public function setOpt($option, $value)
    {
        $success = curl_setopt($this->curl, $option, $value);
        if ($success) {
            $this->options[$option] = $value;
        }
        return $success;
    }

    /**
     * @inheritDoc
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $key = trim($key);
            $value = trim($value);
            $this->headers[$key] = $value;
        }

        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        $this->setOpt(CURLOPT_HTTPHEADER, $headers);
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function setTimeout($seconds = FlagshipConstant::REQUEST_TIME_OUT)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
        return $this;
    }

    /**
     * Set Url
     *
     * @param  $url
     * @param  $mixedData
     * @return HttpClientInterface
     */
    private function setUrl($url, $mixedData = '')
    {
        $built_url = $this->buildUrl($url, $mixedData);
        $this->setOpt(CURLOPT_URL, $built_url);
        return $this;
    }

    /**
     * Exec
     *
     * @return mixed Returns the value provided by parseResponse.
     * @throws Exception
     */
    public function exec()
    {
        $rawResponse = curl_exec($this->curl);
        $curlErrorCode = curl_errno($this->curl);
        $curlErrorMessage = curl_error($this->curl);

        $httpStatusCode = $this->getInfo(CURLINFO_HTTP_CODE);
        $httpError = in_array(floor($httpStatusCode / 100), [4, 5]);

        $response = $this->parseResponse($rawResponse);
        curl_close($this->curl);

        if ($httpError) {
            throw new Exception($curlErrorMessage, $curlErrorCode);
        } else {
            return  $response;
        }
    }

    /**
     * Get
     *
     * @param $url
     * @param array $params
     *
     * @return mixed value provided by exec.
     * @throws Exception
     */
    public function get($url, array $params = [])
    {
        $this->setUrl($url, $params);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        return  $this->exec();
    }

    /**
     * @param  $url
     * @param  array $params
     * @param  array $data
     * @return mixed
     * @throws Exception
     */
    public function post($url, array $params = [], array $data = [])
    {
        $this->setUrl($url, $params);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, json_encode($data));
        return $this->exec();
    }

    /**
     * Get Info
     *
     * @access public
     * @param  $opt
     *
     * @return mixed
     */
    private function getInfo($opt = null)
    {
        return curl_getinfo($this->curl, $opt);
    }

    private function parseResponse($rawResponse)
    {
        return json_decode($rawResponse, true);
    }

    /**
     * Build Url
     *
     * @access public
     * @param  $url
     * @param  $mixedData
     *
     * @return string
     */
    private function buildUrl($url, $mixedData = '')
    {
        $queryString = '';
        if (!empty($mixedData)) {
            $queryMark = strpos($url, '?') > 0 ? '&' : '?';
            if (is_string($mixedData)) {
                $queryString .= $queryMark . $mixedData;
            } elseif (is_array($mixedData)) {
                $queryString .= $queryMark . http_build_query($mixedData, '', '&');
            }
        }
        return $url . $queryString;
    }
}
