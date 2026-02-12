<?php
namespace Juzdy\Http\Middleware\Proxy;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\Middleware\MiddlewareException;
use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Psr\Container\ContainerInterface;

class MiddlewareProxy implements MiddlewareInterface
{

    /**
     * @var string The service identifier for the middleware to be resolved from the container
     */
    private string $middlewareId = '';

    /**
     * Constructor
     *
     * @param ContainerInterface $container The container to resolve the middleware from
     */
    public function __construct(private ContainerInterface $container)
    { 
    }

    /**
     * Set the middleware ID to be resolved from the container.
     *
     * @param string $id The middleware service identifier
     * @return static
     */
    public function withId(string $id): static
    {
        $this->middlewareId = $id;

        return $this;
    }

    /**
     * Get the container instance.
     *
     * @return ContainerInterface The container instance
     */
    private function getContainer(): ContainerInterface
    {
        return $this->container;
    }


    /**
     * Get the middleware ID.
     *
     * @return string The middleware service identifier
     * @throws MiddlewareException If the middleware ID is not set
     */
    private function getId(): string
    {
        if (empty($this->middlewareId)) {
            throw new MiddlewareException('Middleware ID is not set.');
        }

        return $this->middlewareId;
    }

    /**
     * Handle the request by processing through the middleware stack.
     *
     * @param RequestInterface $request
     * @param HandlerInterface $handler
     * 
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        $middlewareId = $this->getId();
        $middleware = $this->getContainer()->get($middlewareId);

        if (!$middleware instanceof MiddlewareInterface) {
            throw new MiddlewareException("Middleware with ID '{$middlewareId}' is not valid.");
        }

        return $middleware->process($request, $handler);
    }
}