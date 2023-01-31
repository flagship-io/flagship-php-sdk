<?php

namespace Flagship\Cache;

interface IHitCacheImplementation
{
    /**
     * This method will be called to cache visitor hits when a hit has failed to be sent if there is no internet, there has been a timeout or if the request responded with something > 2XX.
     * @param array $hits
     * @return void
     */
    public function cacheHit(array $hits);

    /**
     * This method will be called to load hits corresponding to visitor ID from your database and trying to send them again in the background.
     * Note: Hits older than 4H will be ignored
     * @return array
     */
    public function lookupHits();

    /**
     * This method will be called to erase the visitor hits cache corresponding to visitor ID from your database.
     * @param array $hitKeys
     * @return void
     */
    public function flushHits(array $hitKeys);

    /**
     * @return void
     */
    public function flushAllHits();
}