<?php

namespace App\Http\Middleware;

use Closure;
use Flagship\Flagship;

class FlagshipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!$request->hasSession()) {
            return $next($request);
        }
        if (!$request->session()->has('flagshipConfig')) {
            return $next($request);
        }

        $config = $request->session()->get('flagshipConfig');

        Flagship::start($config->getEnvId(), $config->getApiKey(), $config);

        return $next($request);
    }
}
