<?php

namespace Flagship\Visitor;

use Flagship\Flag\FSFlagInterface;
use Flagship\Flag\FSFlagCollectionInterface;
use Flagship\Model\FetchFlagsStatusInterface;

/**
 * Flagship visitor representation.
 *
 * @package Flagship
 */
interface VisitorInterface extends VisitorCoreInterface
{
  /**
   * Visitor unique identifier
   *
   * @return ?string
   */
    public function getVisitorId(): ?string;

  /**
   * Visitor anonymous id
   *
   * @return ?string
   */
    public function getAnonymousId(): ?string;

  /**
   * Return True if the visitor has consented for private data usage, otherwise return False.
   *
   * @return boolean
   */
    public function hasConsented(): bool;



  /**
   * Get the current context
   *
   * @return array<string, mixed>
   */
    public function getContext(): array;

  /**
   * Clear the current context and set a new context value
   * @param  array<string, string|int|bool|float> $context
   *     Collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
   * @return void
   */
    public function setContext(array $context): void;


  /**
   * @param string $key key associated to the flag
   * @return FSFlagInterface
   */
    public function getFlag(string $key): FSFlagInterface;

  /**
   * Returns a Flag object by its key. If no flag matches the given key, an empty flag will be returned.
   * @return FSFlagCollectionInterface
   */
    public function getFlags(): FSFlagCollectionInterface;

  /**
   * @return FetchFlagsStatusInterface
   */
    public function getFetchStatus(): FetchFlagsStatusInterface;
}
