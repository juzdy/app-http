<?php

namespace Juzdy\Http\Handler\Demo\Middleware;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

class DemoHandlerMiddleware implements MiddlewareInterface
{
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        echo(static::class . " applied! This middleware is executed before the handler. You can perform any pre-processing logic here.\n");
        
        // You can perform any pre-processing logic here, such as logging, modifying the request, etc.
        // For demonstration, we'll just add a custom header to the response.

        $response = $handler->handle($request);
        //$response->header('X-Demo-Middleware', 'This response was processed by DemoHandlerMiddleware');

        return $response;
    }
}