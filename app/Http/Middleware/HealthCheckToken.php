<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HealthCheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Health-Token');
        $expectedToken = config('app.health_check_token');

        // Allow in development without token ONLY if not set
        if (app()->environment('local') && !$expectedToken) {
            return $next($request);
        }

        // Validate token using hash_equals to prevent timing attacks
        if (!$token || !hash_equals((string)$expectedToken, (string)$token)) {
            Log::warning('Unauthorized health check attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'token_provided' => $token ? 'yes' : 'no',
            ]);

            abort(403, 'Access denied');
        }

        return $next($request);
    }
}