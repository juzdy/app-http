<?php
namespace Juzdy\Http;

use Juzdy\Http\Middleware\MiddlewareInterface;

/**
 * PSR-15-inspired Request Handler Interface
 * 
 * Handles a server request and produces a response.
 */
interface HandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param RequestInterface $request The incoming request
     * 
     * @return ResponseInterface The generated response
     */
    public function handle(RequestInterface $request): ResponseInterface;
}