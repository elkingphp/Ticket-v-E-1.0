<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't enforce check for admin or if the path is to the maintenance settings
        if (get_setting('maintenance_mode') === '1') {
            $user = auth()->user();
            $isAdmin = $user && ($user->hasRole('admin') || $user->hasRole('super-admin'));

            if (!$isAdmin) {
                // Allow login/logout and auth related routes so admins can log in to turn it off
                $allowedPaths = [
                    'login',
                    'logout',
                    'password/*',
                    'register', // maybe don't allow registration?
                ];

                foreach ($allowedPaths as $path) {
                    if ($request->is($path)) {
                        return $next($request);
                    }
                }

                return response()->view('core::maintenance', [
                    'message' => get_setting('maintenance_message', 'System is currently under maintenance. Please check back later.')
                ], 503);
            }
        }

        return $next($request);
    }
}
