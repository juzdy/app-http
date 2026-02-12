<?php
namespace Juzdy\Http\Middleware\Common;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

/**
 * Session Middleware
 * Manages PHP sessions for incoming HTTP requests.
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Continue to next middleware or handler
        $response = $handler->handle($request);

        // Optionally, you can write session data here if needed
        session_write_close();

        return $response;
    }
}