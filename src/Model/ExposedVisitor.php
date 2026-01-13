<?php

namespace Flagship\Model;

/**
 *
 */
class ExposedVisitor implements ExposedVisitorInterface
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var ?string
     */
    private ?string $anonymousId;

    /**
     * @var array<string, scalar>
     */
    private array $context;

    /**
     * @param string $id
     * @param ?string $anonymousId
     * @param array<string, scalar> $context
     */
    public function __construct(string $id, ?string $anonymousId, array $context)
    {
        $this->id = $id;
        $this->anonymousId = $anonymousId;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return ?string
     */
    public function getAnonymousId(): ?string
    {
        return $this->anonymousId;
    }

    /**
     * @return array<string, scalar>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
