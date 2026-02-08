<?php
namespace Juzdy\Http\Middleware;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;

/**
 * PSR-15-inspired Middleware Interface
 * 
 * Middleware components process HTTP requests in a stack-based manner.
 */
interface MiddlewareInterface// extends PsrMiddlewareInterface
{
    /**
     * Process an incoming request.
     *
     * Processes an incoming request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param RequestInterface $request The incoming request
     * @param HandlerInterface $handler The request handler
     * @return ResponseInterface The generated response
     */
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface;
}
