<?php

namespace Flagship\Flag;

use Flagship\Flag\FSFlagInterface;

interface IFSFlagCollection
{
    /**
     * Gets the number of flags in the collection.
     */
    public function getSize();

    /**
     * Retrieves the flag associated with the specified key.
     * @param string $key - The key of the flag to retrieve.
     * @return FSFlagInterface The flag associated with the specified key, or an empty if the key is not found.
     */
    public function get($key);

    /**
     * Checks if the collection contains a flag with the specified key.
     * @param string $key - The key to check.
     * @return bool True if the collection contains a flag with the specified key, false otherwise.
     */
    public function has($key);

    /**
     * Gets the keys of all flags in the collection.
     * @return array A set of all keys in the collection.
     */
    public function keys();

    /**
     * Filters the collection based on a predicate function.
     * @param callable $predicate - The predicate function used to filter the collection.
     * @return IFSFlagCollection A new IFSFlagCollection containing the flags that satisfy the predicate.
     */
    public function filter(callable $predicate);

    /**
     * Exposes all flags in the collection.
     */
    public function exposeAll();

    /**
     * Retrieves the metadata for all flags in the collection.
     * @return array A map containing the metadata for all flags in the collection.
     */
    public function getMetadata();

    /**
     * Serializes the metadata for all flags in the collection.
     * @return array An array of serialized flag metadata.
     */
    public function toJSON();

    /**
     * Iterates over each flag in the collection.
     * @param callable $callbackfn - The function to execute for each flag.
     */
    public function forEach(callable $callbackfn);
}
