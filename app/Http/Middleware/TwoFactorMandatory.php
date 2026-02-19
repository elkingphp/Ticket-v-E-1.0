<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMandatory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Safety Check: If not authenticated, proceed (let 'auth' middleware handle it)
        if (!$user) {
            return $next($request);
        }

        // 2. Architectural Bypass: Super Admin bypasses ALL enforcement logic
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // 3. Sensitive Role Enforcement (e.g., 'admin' role)
        // Note: super-admin is already bypassed above
        if ($user->hasRole('admin')) {
            // Check if 2FA is NOT enabled (using Fortify's two_factor_secret)
            if (!$user->two_factor_secret) {

                // 4. Prevent Infinite Loops
                if ($this->shouldSkipEnforcement($request)) {
                    return $next($request);
                }

                Log::info('Security Enforcement: Redirecting user to profile for mandatory 2FA.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'route' => $request->path(),
                    'role' => 'admin'
                ]);

                return redirect()->route('profile.index')
                    ->with('error', 'يجب تفعيل التحقق الثنائي (2FA) للوصول إلى هذه الصفحة نظراً لصلاحياتك الحساسة.');
            }
        }

        return $next($request);
    }

    /**
     * Determine if the request should skip enforcement to avoid redirect loops.
     */
    protected function shouldSkipEnforcement(Request $request): bool
    {
        return $request->is('user/two-factor-authentication') ||
            $request->is('user/profile*') ||
            $request->is('logout') ||
            $request->is('runtime-check') ||
            $request->routeIs('profile.index');
    }
}