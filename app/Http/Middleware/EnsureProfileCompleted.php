<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Safety Check: If not authenticated, proceed
        if (!$user) {
            return $next($request);
        }

        // 2. Architectural Bypass: Super Admin bypasses ALL profile enforcement logic
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // 3. Profile Completion Check for regular users
        // Requirement: User must have a phone number and an avatar
        if (!$user->phone || !$user->avatar) {

            // 4. Prevent Infinite Loops
            if ($this->shouldSkipEnforcement($request)) {
                return $next($request);
            }

            Log::info('Profile Enforcement: Redirecting user to complete profile.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'missing' => [
                    'phone' => (bool)$user->phone,
                    'avatar' => (bool)$user->avatar
                ]
            ]);

            return redirect()->route('profile.index')
                ->with('error', 'يرجى إكمال بيانات ملفك الشخصي (رقم الهاتف وصورة الحساب) للمتابعة.');
        }

        return $next($request);
    }

    /**
     * Determine if the request should skip enforcement to avoid redirect loops.
     */
    protected function shouldSkipEnforcement(Request $request): bool
    {
        return $request->is('user/profile*') ||
            $request->is('logout') ||
            $request->is('profile/avatar') ||
            $request->routeIs('profile.index');
    }
}