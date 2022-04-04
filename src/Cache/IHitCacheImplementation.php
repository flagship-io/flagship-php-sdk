<?php

namespace Flagship\Cache;

interface IHitCacheImplementation
{
    /**
     * This method will be called to cache visitor hits when a hit has failed to be sent if there is no internet, there has been a timeout or if the request responded with something > 2XX.
     * @param string $visitorId
     * @param array $data
     * @return void
     */
    public function cacheHit($visitorId, array $data);

    public function lookupHits($visitorId);
}