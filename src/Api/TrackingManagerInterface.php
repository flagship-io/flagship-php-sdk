<?php

namespace Flagship\Api;

interface TrackingManagerInterface extends TrackingManagerCommonInterface
{
    /**
     * @return void
     */
    public function sendBatch();
}