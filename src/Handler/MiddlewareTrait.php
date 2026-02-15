<?php

namespace Juzdy\Http\Handler;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\Middleware\MiddlewarePipeline;

trait MiddlewareTrait
{
    /**
     * @var MiddlewareInterface[]
     */
    protected array $middleware = [];
    
    /**
     * Register middleware for this controller.
     * Override this method in child controllers to add middleware.
     *
     * @return void
     */
    protected function registerMiddleware(): void
    {
        // Override in child controllers to register middleware
    }

    /**
     * Add middleware to this controller.
     *
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function addMiddleware(MiddlewareInterface ...$middleware): static
    {
        array_push($this->middleware, ...$middleware);
        
        return $this;
    }

    /**
     * Execute the controller with its middleware.
     * This method should be called instead of the action method directly.
     *
     * @param callable $action The action to execute after middleware
     * @return void
     */
    public function executeWithMiddleware(callable $action, RequestInterface $request): ResponseInterface
    {
        if (empty($this->middleware)) {
            // No middleware, execute action directly
            return $action($request);
        }

        // Create a pipeline for controller middleware
        $pipeline = new MiddlewarePipeline();
        
        foreach ($this->middleware as $middleware) {
            $pipeline->pipe($middleware);
        }

        // Set the action as the fallback handler
        $handler = new class($action) implements HandlerInterface {
            private $action;

            public function __construct(callable $action)
            {
                $this->action = $action;
            }

            public function handle(RequestInterface $request): ResponseInterface
            {
                return ($this->action)($request);
            }
        };

        $pipeline->setFallbackHandler($handler);
        
        return $pipeline->handle($request);
    }
}