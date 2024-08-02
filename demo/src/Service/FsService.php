<?php

namespace App\Service;

use Flagship\Flagship;

class FsService
{
    public function startSdk(){
        Flagship::start("c1ndrd07m0300ro0jf20", "QzdTI1M9iqaIhnJ66a34C5xdzrrvzq6q8XSVOsS6");
    }

    public function closeSdk(){
        Flagship::close();
    }

    public function createFsVisitor(string $visitorId, array $context){
        return Flagship::newVisitor($visitorId, true)->setContext($context)->build();
    }
}