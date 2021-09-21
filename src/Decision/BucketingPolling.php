<?php

namespace Flagship\Decision;

use Exception;
use Flagship\Enum\FlagshipConstant;
use Flagship\Utils\HttpClient;
use Flagship\Utils\HttpClientInterface;

class BucketingPolling
{
    private $envId;
    private $pollingInterval;
    private $bucketingDirectory;
    /**
     * @var string
     */
    private $lastModified;
    /**
     * @var HttpClient
     */
    private $httpClient;
    /**
     * @var null
     */
    private $configFile;

    /**
     * @param string $envId
     * @param int $pollingInterval
     * @param HttpClientInterface $httpClient
     * @param string $bucketingDirectory
     * @param string $configFile
     */
    public function __construct(
        $envId,
        $pollingInterval,
        HttpClientInterface $httpClient,
        $bucketingDirectory = null,
        $configFile = null
    ) {
        $this->envId = $envId;
        $this->pollingInterval = $pollingInterval;
        $this->httpClient = $httpClient;
        $this->bucketingDirectory = $this->getBucketingDirectory($bucketingDirectory);
        $this->configFile = $configFile;
    }

    private function getBucketingDirectory($directory)
    {
        return $directory ? __DIR__ . '/../../../../../' .
            $directory : __DIR__ . '/../../' . FlagshipConstant::BUCKETING_DIRECTORY;
    }

    /**
     *
     */
    private function checkAndUpdateConfigField()
    {
        if (file_exists($this->configFile)) {
            $fileContent = file_get_contents($this->configFile);
            $configArray = json_decode($fileContent, true);
            if (!empty($configArray['envId'])) {
                $this->envId = $configArray['envId'];
            }
            if (
                isset($configArray['pollingInterval']) && is_numeric($configArray['pollingInterval'])
                && $configArray['pollingInterval'] >= 0
            ) {
                $this->pollingInterval = $configArray['pollingInterval'];
            }
            if (!empty($configArray['bucketingPath'])) {
                $this->bucketingDirectory = $this->getBucketingDirectory($configArray['bucketingPath']);
            }
        }
    }

    public function startPolling()
    {
        $condition = true;
        if ($this->pollingInterval == 0) {
            $condition = false;
        }
        do {
            try {
                $this->checkAndUpdateConfigField();
                echo 'Polling start' . PHP_EOL;
                $url = sprintf(FlagshipConstant::BUCKETING_API_URL, $this->envId);

                if ($this->lastModified) {
                    $this->httpClient->setHeaders([
                        'if-modified-since' => gmdate("'D, d M Y H:i:s \G\M\T'", strtotime($this->lastModified))
                    ]);
                }

                $response = $this->httpClient->get($url);

                $bucketingFile = $this->bucketingDirectory . "/bucketing.json";

                if (!is_dir($this->bucketingDirectory)) {
                    mkdir($this->bucketingDirectory, 0777, true);
                }

                $responseHeaders = $response->getHeaders();
                if (isset($responseHeaders["last-modified"])) {
                    $this->lastModified = $responseHeaders["last-modified"];
                }

                if ($response->getBody()) {
                    file_put_contents($bucketingFile, json_encode($response->getBody()));
                }
                echo 'Polling end' . PHP_EOL;
                sleep($this->pollingInterval / 1000);
            } catch (Exception $exception) {
                fwrite(
                    STDERR,
                    $exception->getMessage() . PHP_EOL
                );
                sleep($this->pollingInterval / 1000);
            }
        } while ($condition);
    }
}
