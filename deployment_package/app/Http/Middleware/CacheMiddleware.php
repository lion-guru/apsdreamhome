<?php
/**
 * APS Dream Home - Cache Middleware
 */

namespace App\Http\Middleware;

use App\Core\Cache;

class CacheMiddleware
{
    private $cache;

    public function __construct()
    {
        $this->cache = Cache::getInstance();
    }

    public function handle($request, $next)
    {
        $cacheKey = 'page_' . md5($request->getUri());
        
        // Check if cached response exists
        $cachedResponse = $this->cache->get($cacheKey);
        if ($cachedResponse) {
            return $cachedResponse;
        }

        // Process request
        $response = $next($request);

        // Cache response for future requests
        $this->cache->set($cacheKey, $response, 1800); // 30 minutes

        return $response;
    }
}
