<?php

namespace App\Http\Middleware;

use Closure;
use Flagship\Visitor;

class FlagshipVisitorMiddleware
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
        if (!$request->session()->isStarted()) {
            return $next($request);
        }
        $visitor = $request->session()->get('visitor');
        if (!$visitor) {
            return response()->json(['error' => 'visitor null'], 200);
        }

        $visitor->synchronizedModifications();

        app()->instance(Visitor::class, $visitor);
        return $next($request);
    }
}
