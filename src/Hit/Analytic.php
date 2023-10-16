<?php

namespace Flagship\Hit;

use Flagship\Enum\HitType;
use Flagship\Hit\Diagnostic;

class Analytic extends Diagnostic
{
    public function __construct()
    {
        parent::__construct(HitType::USAGE);
    }
}