<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class CheckFlagshipSession
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        $message = ['error' => 'First, set your Flagship Environment ID & API Key'];
        if (!$request->hasSession()) {
            Log::error("Flagship", $message);
            return response()->json($message);
        }
        if (!$request->session()->has('flagshipConfig')) {
            Log::error("Flagship", $message);
            return response()->json($message);
        }
        return $next($request);
    }
}
