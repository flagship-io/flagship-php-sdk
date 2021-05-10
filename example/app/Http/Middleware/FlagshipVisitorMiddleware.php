<?php

namespace App\Http\Middleware;

use Closure;
use Flagship\Flagship;
use Flagship\FlagshipConfig;
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
        $visitor = $request->session()->get('visitor');
        $visitor->synchronizedModifications();
        app()->instance(Visitor::class, $visitor);
        return $next($request);
    }
}
