<?php

namespace Flagship\Flag;

use Iterator;

interface FSFlagCollectionInterface extends Iterator
{
    /**
     * Gets the number of flags in the collection.
     * @return int The number of flags in the collection.
     */
    public function getSize(): int;

    /**
     * Retrieves the flag associated with the specified key.
     * @param string $key - The key of the flag to retrieve.
     * @return FSFlagInterface The flag associated with the specified key, or an empty if the key is not found.
     */
    public function get(string $key): FSFlagInterface;

    /**
     * Checks if the collection contains a flag with the specified key.
     * @param string $key - The key to check.
     * @return bool True if the collection contains a flag with the specified key, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Gets the keys of all flags in the collection.
     * @return string[] A set of all keys in the collection.
     */
    public function keys(): array;

  /**
 * Filters the collection based on a predicate function.
 *
 * @param callable $predicate A function that accepts three parameters:
 *                            1. FSFlagInterface $flag - The current flag being processed.
 *                            2. string $key - The key of the current flag.
 *                            3. FSFlagCollectionInterface $collection - The collection the flag belongs to.
 *
 * The function should return true to keep the flag, or false otherwise.
 *
 * @return FSFlagCollectionInterface A new FSFlagCollectionInterface instance containing
   * only the flags for which the predicate function returned true.
 */
    public function filter(callable $predicate): FSFlagCollectionInterface;

    /**
     * Exposes all flags in the collection.
     * @return void
     */
    public function exposeAll(): void;

    /**
     * Retrieves the metadata for all flags in the collection.
     * @return array<string, FSFlagMetadataInterface> A map containing the metadata for all flags in the collection.
     */
    public function getMetadata(): array;

    /**
     * Serializes the metadata for all flags in the collection.
     * @return string A json string array of serialized flag metadata.
     */
    public function toJSON(): string;

    /**
     * Iterates over each flag in the collection.
     * @param callable $callbackFn - The function to execute for each flag.
     *
     * This function accepts three parameters:
     *                             1. FSFlagInterface $flag - The current flag being processed.
     *                             2. string $key - The key of the current flag.
     *                             3. FSFlagCollectionInterface $collection - The collection the flag belongs to.
     */
    public function each(callable $callbackFn);
}
