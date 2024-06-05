<?php

namespace Flagship\Visitor;

use Flagship\Model\FlagDTO;
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
   * Clear the current context and set a new context value
   * @param  array $context : collection of keys, values. e.g: ["age"=>42, "vip"=>true, "country"=>"UK"]
   * @return void
   */
  public function setContext(array $context);


  /**
   * @param string $key key associated to the flag
   * @return FSFlagInterface
   */
  public function getFlag($key);

  /**
   * Returns a Flag object by its key. If no flag matches the given key, an empty flag will be returned.
   * @return FSFlagCollectionInterface
   */
 public function getFlags();

  /**
   * @return FetchFlagsStatusInterface
   */
  public function getFetchStatus();

}
