<?php
namespace Juzdy\Http\Middleware\Proxy;

use Psr\Container\ContainerInterface;
use Juzdy\Container\Attribute\Shared;
use Juzdy\Container\Contract\Lifecycle\SharedInterface;

#[Shared()]
class MiddlewareProxyFactory implements SharedInterface
{
    
    /**
     * Constructor
     *
     * @param ContainerInterface $container The container to resolve the middleware from
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Create a new MiddlewareProxy instance.
     *
     * @return MiddlewareProxy A new instance of MiddlewareProxy
     */
    public function create(string $id): MiddlewareProxy
    {
        $middlewareProxy = $this->getContainer()->get(MiddlewareProxy::class);
        
        return $middlewareProxy->withId($id);
    }

    /**
     * Get the container instance.
     *
     * @return ContainerInterface The container instance
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}