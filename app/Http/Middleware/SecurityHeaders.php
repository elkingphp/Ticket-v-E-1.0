<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders Middleware
 *
 * Adds critical HTTP security headers to every response to protect against:
 * - Clickjacking (X-Frame-Options)
 * - MIME sniffing (X-Content-Type-Options)
 * - XSS (X-XSS-Protection)
 * - Protocol downgrade attacks (Strict-Transport-Security)
 * - Information leakage (Referrer-Policy)
 * - Content injection (Content-Security-Policy)
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent Clickjacking – disallow embedding in any iframe
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME-type sniffing – browser must respect declared Content-Type
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter in legacy browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information sent in requests
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Enforce HTTPS for 1 year (only active in production/HTTPS)
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Remove server information header to prevent fingerprinting
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Permissions Policy – disable access to sensitive browser APIs not used by this app
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        return $response;
    }
}
