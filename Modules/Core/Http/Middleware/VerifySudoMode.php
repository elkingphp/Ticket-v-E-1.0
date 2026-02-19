<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class VerifySudoMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lastVerified = Session::get('auth.sudo_verified_at');
        $ttl = config('auth.sudo_mode_ttl', 600); // Default 10 minutes

        if (!$lastVerified || (time() - $lastVerified) > $ttl) {
            // Only store intended URL for GET requests, as we can't redirect to a POST/PUT/DELETE
            if ($request->isMethod('GET')) {
                Session::put('auth.sudo_intended_url', $request->fullUrl());
            }

            if ($request->expectsJson() || $request->ajax() || $request->hasHeader('X-Requested-With')) {
                return response()->json([
                    'message' => __('core::profile.sudo_title'),
                    'type' => 'sudo_required'
                ], 403);
            }

            return redirect()->route('profile.sudo');
        }

        return $next($request);
    }
}