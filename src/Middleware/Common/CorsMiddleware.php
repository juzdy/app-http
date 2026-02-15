<?php
namespace Juzdy\Http\Middleware\Common;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Juzdy\Http\Middleware\MiddlewareInterface;

/**
 * CORS Middleware
 * 
 * Handles Cross-Origin Resource Sharing (CORS) headers.
 * 
 * Note: This example uses '*' for Access-Control-Allow-Origin.
 * In production, configure specific allowed origins for security.
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * Constructor
     * 
     * @param array $allowedOrigins List of allowed origins. Use ['*'] to allow all (not recommended for production)
     */
    public function __construct(
        private array $allowedOrigins = ['*']
    ) {
    }

    /**
     * Process the request.
     *
     * @param RequestInterface $request
     * @param HandlerInterface $handler
     * 
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        // Handle preflight requests
        //@todo: use response instance?
        if ($request->method() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Continue to next middleware or handler
        $response = $handler->handle($request);

        $origin = $request->server('HTTP_ORIGIN') ?? $request->server('HTTP_HOST');
        
        
        // Set CORS headers
        if (in_array('*', $this->allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', '*');
        } elseif ($origin && in_array($origin, $this->allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Vary', 'Origin');
        }
        
        $response
            ->header(
                'Access-Control-Allow-Methods',
                $response->header('Access-Control-Allow-Methods') 
                ?:
                'GET, POST, PUT, DELETE, OPTIONS'
            )
            ->header(
                'Access-Control-Allow-Headers',
                $response->header('Access-Control-Allow-Headers') 
                ?:
                'Content-Type, Authorization'
            )
            ->header(
                'Access-Control-Allow-Credentials',
                $response->header('Access-Control-Allow-Credentials') 
                ?:
                'true'
            );

        return $response;
    }
}
