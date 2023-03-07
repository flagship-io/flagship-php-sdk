<?php

namespace Flagship\Utils;

interface ContainerInterface
{
    /**
     * @param $id
     * @param $args
     * @param $isFactory
     * @return mixed|object|null
     */
    public function get($id, $args = null, $isFactory = false);
}
