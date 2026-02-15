<?php
namespace Juzdy\Http\Middleware\Common;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

/**
 * Security Headers Middleware
 * 
 * Adds security-related HTTP headers to the response.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        
        // Continue to next middleware or handler
        $response = $handler->handle($request);
        
        // Set security headers
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'no-referrer');
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self'; connect-src 'self'");

        return $response;
    }
}
