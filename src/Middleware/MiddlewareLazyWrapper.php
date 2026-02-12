<?php
namespace Juzdy\Http\Middleware;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Psr\Container\ContainerInterface;

#[Prototype]
class MiddlewareLazyWrapper implements MiddlewareInterface
{
    private ?MiddlewareInterface $middleware = null;
    private ?string $id = null;

    /**
     * MiddlewareLazyWrapper constructor.
     *
     * @param ContainerInterface $container The container to fetch the middleware from
     */
    public function __construct(private ContainerInterface $container)
    {
        
    }

    /**
     * Set the middleware ID to fetch from the container.
     *
     * @param string $id The service ID of the middleware in the container
     * @return $this
     */
    public function withId(string $id): static
    {
        $this->id = $id;

        return $this;   
    }

    /**
     * Get the middleware instance, fetching it from the container if not already loaded.
     *
     * @return string The middleware ID
     * @throws MiddlewareException If the middleware ID is not set
     */
    private function getId(): string
    {
        if (!$this->id) {
            throw new MiddlewareException("Middleware ID not set for MiddlewareLazyWrapper.");
        }
        return $this->id;
    }

    /**
     * Fetch the middleware instance from the container if it hasn't been loaded yet.
     *
     * @return MiddlewareInterface The middleware instance
     * @throws MiddlewareException If the middleware cannot be found or does not implement MiddlewareInterface
     */
    private function getMiddleware(): MiddlewareInterface
    {
        if (!$this->middleware) {
            $id = $this->getId();
            if (!$this->container->has($id)) {
                throw new MiddlewareException("Middleware with ID '{$id}' not found in container.");
            }
            $middleware = $this->container->get($id);
            if (!$middleware instanceof MiddlewareInterface) {
                throw new MiddlewareException("Service with ID '{$id}' must implement MiddlewareInterface.");
            }
            $this->middleware = $middleware;
        }
        return $this->middleware;
    }

    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        return $this->getMiddleware()->process($request, $handler);
    }
}
