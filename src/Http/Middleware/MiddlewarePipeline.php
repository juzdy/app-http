<?php
namespace Juzdy\Http\Middleware;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

/**
 * Middleware Pipeline
 * 
 * Processes a request through a stack of middleware components.
 */
class MiddlewarePipeline //implements HandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middleware = [];

    /**
     * @var HandlerInterface|null
     */
    private ?HandlerInterface $fallbackHandler = null;

    /**
     * Add middleware to the pipeline.
     *
     * @param MiddlewareInterface $middleware
     * @return static
     */
    public function pipe(MiddlewareInterface $middleware): static
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Set the fallback handler.
     *
     * @param HandlerInterface $handler
     * @return static
     */
    public function setFallbackHandler(HandlerInterface $handler): static
    {
        $this->fallbackHandler = $handler;
        
        return $this;
    }

    /**
     * Handle the request by processing through the middleware stack.
     *
     * @param RequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->processMiddleware($request, 0);
    }

    /**
     * Process middleware at the given index.
     *
     * @param RequestInterface $request
     * @param int $index
     * @return ResponseInterface
     */
    public function processMiddleware(RequestInterface $request, int $index): ResponseInterface
    {
        // If no more middleware, call the fallback handler
        if (!isset($this->middleware[$index])) {
            if ($this->fallbackHandler == null) {
                throw new \RuntimeException('No fallback handler set for middleware pipeline.');
            }
            return $this->fallbackHandler->handle($request);
        }

        // Create a handler for the next middleware in the stack
        $next = new class($this, $request, $index + 1) implements HandlerInterface {
            private MiddlewarePipeline $pipeline;
            private RequestInterface $request;
            private int $nextIndex;

            public function __construct(MiddlewarePipeline $pipeline, RequestInterface $request, int $nextIndex)
            {
                $this->pipeline = $pipeline;
                $this->request = $request;
                $this->nextIndex = $nextIndex;
            }

            public function handle(RequestInterface $request): ResponseInterface
            {
                 return $this->pipeline->processMiddleware($request, $this->nextIndex);
            }
        };

        // Process current middleware
        return $this->middleware[$index]->process($request, $next);
    }
}
