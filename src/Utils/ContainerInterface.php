<?php

namespace Flagship\Utils;

interface ContainerInterface
{
    public function get($id, $args = null, $isFactory = false);

    public function has($id);
}
