<?php

namespace Flagship\Hit;

use Flagship\Enum\HitType;

class UsageHit extends Diagnostic
{
    public function __construct()
    {
        parent::__construct(HitType::USAGE);
    }
}
