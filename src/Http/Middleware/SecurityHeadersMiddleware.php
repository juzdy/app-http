<?php
namespace App\Middleware;

use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\Middleware\RequestHandlerInterface;
use Juzdy\Request;

/**
 * Security Headers Middleware
 * 
 * Adds security-related HTTP headers to the response.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * Process the request.
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return void
     */
    public function process(Request $request, RequestHandlerInterface $handler): void
    {
        // Add security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Continue to next middleware or handler
        $handler->handle($request);
    }
}
