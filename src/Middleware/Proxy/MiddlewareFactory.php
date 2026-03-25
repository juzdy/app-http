<?php
namespace Juzdy\Http\Middleware\Proxy;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareFactory
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
     * Get the container instance.
     *
     * @return ContainerInterface The container instance
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Create a middleware instance by its service identifier.
     *
     * @param string $id The service identifier of the middleware to create
     * @return MiddlewareInterface The created middleware instance
     */
    public function create(string $id): MiddlewareInterface
    {
        return $this->getContainer()->get($id);
    }
}