<?php

namespace App\Http\Middleware;

use Closure;
use Flagship\Flagship;

class StartFlagship
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
        $config = $request->session()->get('flagshipConfig');

        Flagship::start($config->getEnvId(), $config->getApiKey(), $config);

        return $next($request);
    }
}
