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
            return response()->json(['error' => 'First, set your Flagship Environment ID & API Key'], 200);
        }
        if (!$request->session()->has('flagshipConfig')) {
            return response()->json(['error' => 'First, set your Flagship Environment ID & API Key'], 200);
        }

        $config = $request->session()->get('flagshipConfig');

        Flagship::start($config->getEnvId(), $config->getApiKey(), $config);

        return $next($request);
    }
}
