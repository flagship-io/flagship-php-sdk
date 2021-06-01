<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Utils\HttpClient;

class BucketingPolling
{
    private $envId;
    private $pollingInterval;
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @param $envId
     * @param $pollingInterval
     */
    public function __construct($envId, $pollingInterval, HttpClient $httpClient)
    {
        $this->envId = $envId;
        $this->pollingInterval = $pollingInterval;
        $this->httpClient = $httpClient;
    }

    public function startPolling()
    {
        $condition = true;
        if ($this->pollingInterval == 0) {
            $condition = false;
        }
        do {
            try {
                echo 'Polling start' . PHP_EOL;
                $url = "https://cdn.flagship.io/{$this->envId}/bucketing.json";
                $response = $this->httpClient->get($url);
                $bucketingFile = __DIR__ . "/../../bucketing.json";
                file_put_contents($bucketingFile, json_encode($response->getBody()));
                echo 'Polling end' . PHP_EOL;
                sleep($this->pollingInterval / 1000);
            } catch (Exception $exception) {
                fwrite(
                    STDERR,
                    $exception->getMessage() . PHP_EOL
                );
            }
        } while ($condition);
    }
}
