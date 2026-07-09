<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Juzdy\Http\Middleware\Exception\MiddlewareStackEnded;
use Juzdy\Http\Middleware\Exception\InvalidArgumentException;
use Juzdy\Container\Attribute\Shared;

/**
 * Middleware Stack
 * 
 * Processes a request through a stack of middleware components.
 */
#[Shared]
class AppMiddlewareStack implements RequestHandlerInterface
{


    /**
     * @var array<int, MiddlewareInterface[]> An array of middleware grouped by priority
     */
    private array $middleware = [];

    /**
     * Add middleware to the stack.
     * @template T of class-string|MiddlewareInterface
     *
     * @param T $middleware
     * @return static
     */
    public function push(MiddlewareInterface|string $middleware, int $priority = 0): static
    {
        if (is_string($middleware) && !is_a($middleware, MiddlewareInterface::class, true)) {
            throw new InvalidArgumentException("Middleware class must implement " . MiddlewareInterface::class);
        }

        $this->middleware[$priority][] = $middleware;

        krsort($this->middleware);

        return $this;
    }

    /**
     * Handle the request by processing through the middleware stack.
     *
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     * 
     * @throws MiddlewareStackEnded If the middleware stack is exhausted without producing a response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->processMiddleware($request);
    }

    /**
     * Process the request through the middleware stack.
     *
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     * 
     * @throws MiddlewareStackEnded If the middleware stack is exhausted without producing a response
     */
    protected function processMiddleware(ServerRequestInterface $request): ResponseInterface
    {
        $next = fn (ServerRequestInterface $req) => throw new MiddlewareStackEnded("Middleware stack has been fully processed. No more middleware to execute.");

        foreach ($this->middleware as $priority => $middlewares) {
            foreach ($middlewares as $middleware) {
                $currentNext = $next;
                $next = fn (ServerRequestInterface $req) => $middleware->process($req, new class($currentNext) implements RequestHandlerInterface {

                    public function __construct(private $handler)
                    {}

                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        return ($this->handler)($request);
                    }
                });
            }
        }

        return $next($request);
    }
}
