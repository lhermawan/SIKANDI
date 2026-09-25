<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Agent) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Endpoint only accessible by registered agents.',
            ], 403);
        }

        return $next($request);
    }
}
