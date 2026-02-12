<?php
namespace Juzdy\Http\Middleware\Common;

use Juzdy\Error\ErrorHandler;
use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

class ErrorHandlerMiddleware implements MiddlewareInterface
{

    public function __construct(private ErrorHandler $errorHandler)
    {
        // You can initialize the error handler here if needed
        // ErrorHandler::init();
    }

    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        // ErrorHandler::init();
        // return $handler->handle($request);

        try {
        
            return $handler->handle($request);

        } catch (\Throwable $e) {
            // Handle the exception and return a proper error response
            $response = new \Juzdy\Http\Response();
            $response->status(500);
            $response->body(
                sprintf(
                    "An unexpected error occurred: %s<pre>\n%s</pre>",
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
            return $response;
        }
    }
}