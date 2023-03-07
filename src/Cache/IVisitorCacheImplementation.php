<?php

namespace Flagship\Cache;

interface IVisitorCacheImplementation
{
    /**
     * This method is called when the SDK needs to cache visitor information in your database.
     * @param string $visitorId
     * @param array $data
     * @return void
     */
    public function cacheVisitor($visitorId, array $data);

    /**
     * This method is called when the SDK needs to get the visitor information corresponding to visitor ID from your database.
     * @param string $visitorId
     * @return array
     */
    public function lookupVisitor($visitorId);

    /**
     * This method is called when the SDK needs to erase the visitor information corresponding to visitor ID in your database.
     * @param string $visitorId
     * @return void
     */
    public function flushVisitor($visitorId);
}
