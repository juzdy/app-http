<?php
namespace Juzdy\Http;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Juzdy\Config;
use Juzdy\App\AppInterface;
use Juzdy\Container\Attribute\Parameter\Using;
use Juzdy\Container\Attribute\Prefer;
use Juzdy\Container\Attribute\Shared;
use Juzdy\Container\Contract\InjectableInterface;
use Juzdy\Container\Contract\Lifecycle\PrototypeInterface;
use Juzdy\Container\Contract\Lifecycle\SharedInterface;
use Juzdy\EventBus\Event\EventInterface;
use Juzdy\Http\Event\BeforeRun;
use Juzdy\Http\Handler\NotFoundHandler;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Juzdy\Http\Middleware\MiddlewarePipeline;

/**
 * HTTP Application implementation
 *
 * @package Juzdy\Http
 */
#[Prefer(
    [
        EventDispatcherInterface::class => \Juzdy\EventBus\EventDispatcher::class,
    ]
)]
#[Shared]
class Http implements AppInterface, InjectableInterface, PrototypeInterface, SharedInterface
{
    /**
     * Constructor
     * 
     * @param ContainerInterface $container
     * @param RequestInterface $request
     * @param EventDispatcherInterface $eventDispatcher
     * @param MiddlewarePipeline $pipeline
     * @param EventInterface $beforeRunEvent
     */    
    public function __construct(
        //private NothingInterface $nothing,
        private ContainerInterface $container,
        private RequestInterface $request,
        private EventDispatcherInterface $eventDispatcher,
        private MiddlewarePipeline $pipeline,
        #[Using(BeforeRun::class)]
        private EventInterface $beforeRunEvent,
    ) {}

    public function __clone(): void
    {
        // Custom clone logic if needed
    }
    

    /**
     * Run the application.
     *
     * @return void
     */
    public function run(): void
    {
        // Dispatch before run event
        $this->getEventDispatcher()->dispatch($this->getBeforeRunEvent());
        
        try{

            $this->loadGlobalMiddleware();
            
            $response = $this->handleRequest();

            $response->send();

        } catch (\Throwable $e) {
            
            throw new \Exception($e);
        }
        
    }

    /**
     * Handle the incoming HTTP request.
     *
     * @return ResponseInterface
     */
    protected function handleRequest(): ResponseInterface
    {
        // 
        $this->getPipeline()->setFallbackHandler(
            $this->getContainer()->get(NotFoundHandler::class)
        );
        
        // Process the request through the middleware pipeline
        return $this->getPipeline()->handle($this->getRequest());
    }

    /**
     * Get the dependency injection container.
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Get the current HTTP request.
     *
     * @return RequestInterface
     */
    protected function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Get the middleware pipeline.
     *
     * @return MiddlewarePipeline
     */
    public function getPipeline(): MiddlewarePipeline
    {
        return $this->pipeline;
    }

    /**
     * Load global middleware from configuration.
     *
     * @return void
     */
    private function loadGlobalMiddleware(): void
    {
        $middleware = Config::get('middleware.global', []);

        foreach ($middleware as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                try {
                    $middleware = $this->getContainer()->get($middlewareClass);
                } catch (NotFoundExceptionInterface) {
                    throw new \Exception("Middleware class {$middlewareClass} could not be resolved.");
                }
                $this->getPipeline()->pipe($middleware);
            }
        }
    }

    /**
     * Get the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Get the before run event.
     *
     * @return EventInterface
     */
    protected function getBeforeRunEvent(): EventInterface
    {
        return $this->beforeRunEvent->attach('app', $this);
    }
    
}