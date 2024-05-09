<?php

namespace Flagship\Visitor;

use Flagship\Model\FlagDTO;
use Flagship\Flag\FlagInterface;
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
   * @return string
   */
  public function getVisitorId();

  /**
   * Visitor anonymous id
   *
   * @return string
   */
  public function getAnonymousId();

  /**
   * Return True if the visitor has consented for private data usage, otherwise return False.
   *
   * @return boolean
   */
  public function hasConsented();



  /**
   * Get the current context
   *
   * @return array
   */
  public function getContext();


  /**
   * @param string $key key associated to the flag
   * @param string|bool|numeric|array $defaultValue flag default value.
   * @return FlagInterface
   */
  public function getFlag($key, $defaultValue);

  /**
   * Return an array of all flags data fetched for the current visitor.
   * @return FlagDTO[]
   */
  public function getFlagsDTO();

/**
 * Set the callback function for when the fetch flags status changes.
 *
 * The callback function should have the following signature:
 * function(FetchFlagsStatusInterface $fetchFlagsStatus): void
 *
 * @param callable $onFetchFlagsStatusChanged The callback function.
 * @return void
 */
  public function setOnFetchFlagsStatusChanged(callable $onFetchFlagsStatusChanged);

  /**
   * @return FetchFlagsStatusInterface
   */
  public function getFetchStatus();

}
