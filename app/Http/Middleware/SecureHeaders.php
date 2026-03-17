<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Security headers to add to all responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy - restrict resource loading
        // Allow inline styles (needed for our CSS vars) but restrict scripts
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",  // Allow inline scripts for simple interactions
            "style-src 'self' 'unsafe-inline'",   // Allow inline styles for CSS variables
            "img-src 'self' data: blob:",         // Allow data URIs for small images, blob for photo capture
            "font-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",             // Prevent clickjacking
            "base-uri 'self'",
            "object-src 'none'",                  // No plugins
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Enable XSS filtering (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Referrer policy - don't leak URLs to external sites
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions policy - disable unused browser features
        $response->headers->set('Permissions-Policy', implode(', ', [
            'camera=(self)',          // Allow camera for photo proof
            'microphone=()',          // No mic needed
            'geolocation=()',         // No location needed  
            'payment=()',             // No payments
            'usb=()',                 // No USB
        ]));

        return $response;
    }
}
