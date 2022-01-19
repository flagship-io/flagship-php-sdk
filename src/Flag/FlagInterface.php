<?php

namespace Flagship\Flag;

interface FlagInterface
{
    /**
     * Returns the value from the assigned campaign variation or the Flag default value if the Flag does not exist,
     * or if types are different.
     *
     * @param bool $userExposed
     * @return bool|numeric|string|array
     */
    public function getValue($userExposed);

    /**
     * This method will return true if a Flag exists in Flagship.
     * @return bool
     */
    public function exists();

    /**
     * Tells Flagship the user have been exposed and have seen this flag.
     * @return void
     */
    public function userExposed();

    /**
     * @return FlagMetadata
     */
    public function getMetadata();
}
