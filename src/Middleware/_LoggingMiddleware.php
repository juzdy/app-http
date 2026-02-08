<?php
namespace App\Middleware;

use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\Middleware\RequestHandlerInterface;
use Juzdy\Request;

/**
 * Logging Middleware
 * 
 * Logs request information for debugging purposes.
 */
class LoggingMiddleware implements MiddlewareInterface
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
        // Log request information (example)
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getRequestMethod(),
            'uri' => $request->getServer('REQUEST_URI'),
            'ip' => $request->getServer('REMOTE_ADDR'),
        ];
        
        // In a real application, you would write this to a log file
        // error_log(json_encode($logData));
        
        // Continue to next middleware or handler
        $handler->handle($request);
    }
}
