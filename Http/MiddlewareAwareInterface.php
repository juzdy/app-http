<?php

namespace Juzdy\Http;

use Juzdy\Http\Middleware\MiddlewareInterface;

interface MiddlewareAwareInterface
{
    /**
     * Add middleware to this controller.
     *
     * @param MiddlewareInterface $middleware   The middleware to add
     * 
     * @return static
     */
    public function addMiddleware(MiddlewareInterface $middleware): static;
}