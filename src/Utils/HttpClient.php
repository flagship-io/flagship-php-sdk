<?php

namespace Flagship\Utils;

use ErrorException;
use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Interfaces\HttpClientInterface;

class HttpClient implements HttpClientInterface
{

    private $curl;

    private $options;

    /**
     * @var array
     */
    private $headers = [];
    /**
     * @var int
     */
    private $attempts;

    /**
     * Construct
     *
     * @access public
     * @throws ErrorException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new ErrorException('cURL library is not loaded');
        }
        $this->curl = curl_init();
        $this->initialize();
    }

    /**
     * Initialize
     *
     * @access private
     */
    private function initialize()
    {
        $this->setTimeout(FlagshipConstant::REQUEST_TIME_OUT);
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
     * @access public
     * @param  $url
     * @param  $mixed_data
     * @return HttpClientInterface
     */
    private function setUrl($url, $mixed_data = '')
    {
        $built_url = $this->buildUrl($url, $mixed_data);
        $this->setOpt(CURLOPT_URL, $built_url);
        return $this;
    }

    /**
     * Exec
     *
     * @access public
     * @return mixed Returns the value provided by parseResponse.
     * @throws Exception
     */
    public function exec()
    {
        $this->attempts += 1;

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
        $args = [];
        $args[] = $this->curl;

        if (func_num_args()) {
            $args[] = $opt;
        }

        return call_user_func_array('curl_getinfo', $args);
    }

    private function parseResponse($raw_response)
    {
        return json_decode($raw_response, true);
    }

    /**
     * Build Url
     *
     * @access public
     * @param  $url
     * @param  $mixed_data
     *
     * @return string
     */
    private function buildUrl($url, $mixed_data = '')
    {
        $query_string = '';
        if (!empty($mixed_data)) {
            $query_mark = strpos($url, '?') > 0 ? '&' : '?';
            if (is_string($mixed_data)) {
                $query_string .= $query_mark . $mixed_data;
            } elseif (is_array($mixed_data)) {
                $query_string .= $query_mark . http_build_query($mixed_data, '', '&');
            }
        }
        return $url . $query_string;
    }

    /**
     * @inheritDoc
     */
    public static function create()
    {
        return new HttpClient();
    }
}
