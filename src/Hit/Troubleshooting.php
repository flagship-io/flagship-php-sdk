<?php

namespace Flagship\Hit;

use Flagship\Enum\HitType;

class Troubleshooting extends Diagnostic
{
    public function __construct()
    {
        parent::__construct(HitType::TROUBLESHOOTING);
    }
}
