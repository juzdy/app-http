<?php
namespace Juzdy\Http\Middleware;

use Juzdy\Error\ErrorHandler;
use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        ErrorHandler::init();
        return $handler->handle($request);

        // try {
        
        //     return $handler->handle($request);
        // } catch (\Throwable $e) {
        //     // Handle the exception and return a proper error response
        //     $response = new \Juzdy\Http\Response();
        //     $response->status(500);
        //     $response->header('Content-Type', 'application/json');
        //     $response->body(json_encode([
        //         'error' => 'Internal Server Error',
        //         'message' => $e->getMessage(),
        //     ]));
        //     return $response;
        // }
    }
}