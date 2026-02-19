<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GenerateRequestId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string)Str::uuid();

        // Attach to request attributes for easy access
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);

        // Attach to response headers for debugging/tracing
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}