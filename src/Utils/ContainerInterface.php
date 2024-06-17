<?php

namespace Flagship\Utils;

interface ContainerInterface
{
    /**
     * @param string $id
     * @param array|null $args
     * @param bool $isFactory
     * @return mixed|object|null
     */
    public function get(string $id, array $args = null, bool $isFactory = false): mixed;
}
