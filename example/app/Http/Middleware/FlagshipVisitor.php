<?php

namespace App\Http\Middleware;

use Closure;
use Flagship\Visitor;
use Illuminate\Support\Facades\Log;

class FlagshipVisitor
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
        if (!$visitor) {
            $message = ['error' => 'visitor null'];
            Log::error('Flagship', $message);
            return response()->json($message);
        }
//        $visitor->synchronizedModifications();
        app()->instance(Visitor::class, $visitor);
        return $next($request);
    }
}
