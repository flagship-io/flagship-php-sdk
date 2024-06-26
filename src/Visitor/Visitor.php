<?php

namespace Flagship\Visitor;

use Flagship\Config\FlagshipConfig;
use Flagship\Flag\FSFlagCollectionInterface;
use Flagship\Flag\FSFlagInterface;
use Flagship\Hit\HitAbstract;
use Flagship\Model\FetchFlagsStatusInterface;
use Flagship\Traits\LogTrait;
use JsonSerializable;

class Visitor implements VisitorInterface, JsonSerializable
{
    use LogTrait;

    /**
     * @var VisitorDelegate
     */
    private VisitorDelegate $visitorDelegate;


    /**
     * Create a new visitor.
     *
     * @param VisitorDelegate $visitorDelegate
     */
    public function __construct(VisitorDelegate $visitorDelegate)
    {
        $this->visitorDelegate = $visitorDelegate;
    }


    /**
     * @return VisitorDelegate
     */
    private function getVisitorDelegate(): VisitorDelegate
    {
        return $this->visitorDelegate;
    }


    /**
     *
     * @return FlagshipConfig
     */
    public function getConfig(): FlagshipConfig
    {
        return $this->getVisitorDelegate()->getConfig();
    }


    /**
     * @inheritDoc
     */
    public function getVisitorId(): string
    {
        return $this->getVisitorDelegate()->getVisitorId();
    }


    public function setVisitorId($visitorId): static
    {
        $this->getVisitorDelegate()->setVisitorId($visitorId);
        return $this;
    }


    /**
     *@inheritDoc
     */
    public function hasConsented(): bool
    {
        return $this->getVisitorDelegate()->hasConsented();
    }


    /**
     * @inheritDoc
     */
    public function setConsent(bool $hasConsented): void
    {
        $this->getVisitorDelegate()->setConsent($hasConsented);
    }


    /**
     * @inheritDoc
     */
    public function getContext(): array
    {
        return $this->getVisitorDelegate()->getContext();
    }


    /**
     * @inheritDoc
     */
    public function setContext(array $context): void
    {
        $this->getVisitorDelegate()->setContext($context);
    }

    /**
     * @inheritDoc
     */
    public function getAnonymousId(): ?string
    {
        return $this->getVisitorDelegate()->getAnonymousId();
    }


    /**
     * @inheritDoc
     */
    public function updateContext(string $key, float|bool|int|string|null $value): void
    {
        $this->getVisitorDelegate()->updateContext($key, $value);
    }


    /**
     * @inheritDoc
     */
    public function updateContextCollection(array $context): void
    {
        $this->getVisitorDelegate()->updateContextCollection($context);
    }


    /**
     * @inheritDoc
     */
    public function clearContext(): void
    {
        $this->getVisitorDelegate()->clearContext();
    }


    /**
     * @inheritDoc
     */
    public function authenticate(string $visitorId): void
    {
        $this->getVisitorDelegate()->authenticate($visitorId);
    }


    /**
     * @inheritDoc
     */
    public function unauthenticate(): void
    {
        $this->getVisitorDelegate()->unauthenticate();
    }


    /**
     * @inheritDoc
     */
    public function sendHit(HitAbstract $hit): void
    {
        $this->getVisitorDelegate()->sendHit($hit);
    }

    public function jsonSerialize(): mixed
    {
        return $this->getVisitorDelegate()->jsonSerialize();
    }


    /**
     * @inheritDoc
     */
    public function fetchFlags(): void
    {
        $this->visitorDelegate->fetchFlags();
    }


    /**
     * @inheritDoc
     */
    public function getFlag(string $key): FSFlagInterface
    {
        return $this->visitorDelegate->getFlag($key);
    }

    /**
     * @inheritDoc
     */
    public function getFlags(): FSFlagCollectionInterface
    {
        return $this->visitorDelegate->getFlags();
    }

    /**
     * @inheritDoc
     */
    public function getFetchStatus(): FetchFlagsStatusInterface
    {
        return $this->visitorDelegate->getFetchStatus();
    }
}
