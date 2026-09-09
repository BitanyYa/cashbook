<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventPageCaching
{
    /**
     * Prevent browser and PWA disk caching for dynamic HTML responses
     * to avoid stale CSRF tokens and expired session issues.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Only add cache-busting headers to HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'text/html') || !$request->expectsJson()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        return $response;
    }
}
