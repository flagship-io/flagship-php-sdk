<?php
//start fsService
//demo/src/Service/FsService.php

namespace App\Service;

use Flagship\Flagship;
use Flagship\Visitor\VisitorInterface;

class FsService
{
    /**
     * Start the Flagship SDK
     *
     * @param string $envId
     * @param string $apiKey
     */
    public function startSdk(string $envId, string $apiKey){
        Flagship::start($envId, $apiKey);
    }

    /**
     * Close the Flagship SDK
     */
    public function closeSdk(){
        Flagship::close();
    }

    /**
     * Create a new visitor
     *
     * @param string $visitorId
     * @param array $context
     * @return VisitorInterface
     */
    public function createFsVisitor(string $visitorId, array $context){
        return Flagship::newVisitor($visitorId, true)->setContext($context)->build();
    }
}
//end fsService