<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Add basic security headers for production.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (app()->environment('production')) {
            // Avoid leaking tokens via Referer
            $response->headers->set('Referrer-Policy', 'no-referrer');

            // Enable HSTS when site is fully HTTPS
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

            // Sensible defaults
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        return $response;
    }
}


