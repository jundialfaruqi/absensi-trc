<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndexMiddleware
{
public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // SEO Protection
        $response->headers->set(
            'X-Robots-Tag',
            'noindex, nofollow, noarchive, nosnippet'
        );

        // Security Headers
        $response->headers->set(
            'X-Frame-Options',
            'SAMEORIGIN'
        );

        $response->headers->set(
            'X-Content-Type-Options',
            'nosniff'
        );

        $response->headers->set(
            'Referrer-Policy',
            'strict-origin-when-cross-origin'
        );

        return $response;
    }
}
