<?php

namespace Flagship\Utils;

use ErrorException;
use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Model\HttpResponse;

class HttpClient implements HttpClientInterface
{

    private $curl;

    private $options = [];

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
    }

    private function curlInit()
    {
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
        if (!$this->curl) {
            $this->curlInit();
        }
        $success = curl_setopt($this->curl, $option, $value);
        if ($success) {
            $this->options[$option] = $value;
        }
        return $success;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
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
     * @param  $data
     * @return HttpClientInterface
     */
    private function setUrl($url, $data = '')
    {
        $builtUrl = $this->buildUrl($url, $data);
        $this->setOpt(CURLOPT_URL, $builtUrl);
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

        curl_close($this->curl);

        $this->curl = null;

        if ($httpError) {
            throw new Exception($curlErrorMessage, $curlErrorCode);
        }

        $response = $this->parseResponse($rawResponse);
        return new HttpResponse($httpStatusCode, $response);

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
        return $this->exec();
    }

    /**
     * @param  $url
     * @param array $params
     * @param array $data
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
     * @param  $data
     *
     * @return string
     */
    private function buildUrl($url, $data = '')
    {
        $queryString = '';
        if (!empty($data)) {
            $queryMark = strpos($url, '?') > 0 ? '&' : '?';
            if (is_string($data)) {
                $queryString .= $queryMark . $data;
            } elseif (is_array($data)) {
                $queryString .= $queryMark . http_build_query($data, '', '&');
            }
        }
        return $url . $queryString;
    }
}
