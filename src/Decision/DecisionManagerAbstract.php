<?php

namespace Flagship\Decision;

use Flagship\Enum\FlagshipStatus;
use Flagship\Utils\HttpClientInterface;
use Flagship\Visitor;

abstract class DecisionManagerAbstract implements DecisionManagerInterface
{
    /**
     * @var bool
     */
    protected $isPanicMode = false;
    /**
     * @var HttpClientInterface
     */
    protected $httpClient;
    /**
     * @var callable
     */
    private $statusChangedCallable;

    /**
     * ApiManager constructor.
     *
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return bool
     */
    public function getIsPanicMode()
    {
        return $this->isPanicMode;
    }

    /**
     * @param bool $isPanicMode
     * @return DecisionManagerAbstract
     */
    public function setIsPanicMode($isPanicMode)
    {
        $status = $isPanicMode ? FlagshipStatus::READY_PANIC_ON : FlagshipStatus::READY;
        $this->updateFlagshipStatus($status);

        $this->isPanicMode = $isPanicMode;
        return $this;
    }

    /**
     * Define a callable in order to get callback when the SDK status has changed.
     * @param callable $statusChangedCallable callback
     * @return DecisionManagerAbstract
     */
    public function setStatusChangedCallable($statusChangedCallable)
    {
        if (is_callable($statusChangedCallable)) {
            $this->statusChangedCallable = $statusChangedCallable;
        }
        return $this;
    }

    protected function updateFlagshipStatus($newStatus)
    {
        $callable = $this->statusChangedCallable;
        if ($callable) {
            call_user_func($callable, $newStatus);
        }
    }

    /**
     * @inheritDoc
     */
    abstract public function getCampaignModifications(Visitor $visitor);
}
