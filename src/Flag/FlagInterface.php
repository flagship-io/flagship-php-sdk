<?php

namespace Flagship\Flag;

use Flagship\Model\ExposedFlagInterface;

interface FlagInterface extends ExposedFlagInterface
{
    /**
     * Returns the value from the assigned campaign variation or the Flag default value if the Flag does not exist,
     * or if types are different.
     *
     * @param bool $visitorExposed
     * @return bool|numeric|string|array
     */
    public function getValue($visitorExposed = true);

    /**
     * This method will return true if a Flag exists in Flagship.
     * @return bool
     */
    public function exists();

    /**
     * Tells Flagship the visitor have been exposed and have seen this flag.
     * @return void
     */
    public function visitorExposed();

    /**
     * @inheritdoc
     */
    public function getMetadata();
}
