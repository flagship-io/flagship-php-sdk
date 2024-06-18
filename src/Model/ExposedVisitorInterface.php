<?php

namespace Flagship\Model;

interface ExposedVisitorInterface
{
    /**
     * Visitor id
     * @return string
     */
    public function getId(): string;

    /**
     * visitor anonymous id
     * @return string|null
     */
    public function getAnonymousId(): ?string;

    /**
     * visitor context
     * @return array<string, mixed>
     */
    public function getContext(): array;
}
