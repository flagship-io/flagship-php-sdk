<?php

namespace Flagship\Flag;

interface FlagInterface
{
    /**
     * Return the current flag value if the flag key exists in Flagship and activate it if needed.
     * @param bool $userExposed
     * @return mixed
     */
    public function value($userExposed);

    /**
     * Return true if the flag exists, false otherwise.
     * @return bool
     */
    public function exists();

    /**
     * Activate the current key
     * @return void
     */
    public function userExposed();

    /**
     * @return FlagMetadata
     */
    public function getMetadata();
}
