<?php

namespace App\Http\Middleware;

use Closure;
use Flagship\Visitor\Visitor;
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
            $message = ['error' => 'Set your Visitor ID and context'];
            Log::error('Flagship', $message);
            return response()->json($message, 422);
        }
        app()->instance(Visitor::class, $visitor);
        return $next($request);
    }
}
