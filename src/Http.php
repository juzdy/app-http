<?php
namespace Juzdy\Http;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Juzdy\App\AppInterface;
use Juzdy\Config\ConfigInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Juzdy\Http\Handler\NotFoundHandler;
use Juzdy\Http\Middleware\MiddlewarePipeline;
use Juzdy\Http\Middleware\Proxy\MiddlewareProxy;
use Juzdy\EventBus\Event\EventInterface;
use Juzdy\Http\Event\AfterRun;
use Juzdy\Http\Event\BeforeRun;
use Juzdy\Container\Attribute\Parameter\Using;
use Juzdy\Container\Attribute\Prefer;
use Juzdy\Container\Attribute\Shared;

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
class Http implements AppInterface
{
    const CONFIG_PATH_MIDDLEWARE_HTTP = 'middleware.http';

    private ?ResponseInterface $response = null;

    
    public function __construct(
        private ConfigInterface $config,
        private ContainerInterface $container,
        private RequestInterface $request,
        private EventDispatcherInterface $eventDispatcher,
        private MiddlewarePipeline $pipeline,
        private MiddlewareProxy $middlewareProxy,
        #[Using(BeforeRun::class)]
        private EventInterface $beforeRunEvent,
        #[Using(AfterRun::class)]
        private EventInterface $afterRunEvent,
    ) {

        $beforeRunEvent->attach([
            'app' => $this,
            'request' => $request,
        ]);

        $afterRunEvent->attach([
            'app'     => $this,
            'request' => $request,
            // 'response' => $this->getResponse(),
        ]);
    }

    /**
     * Magic method to allow direct access to services from the container.
     *
     * @param string $service The service identifier
     * @return mixed The resolved service instance
     */
    public function __invoke(string $service): mixed
    {
        return $this->getContainer()->get($service);
    }

    /**
     * Prevent cloning of the Http instance.
     */
    private function __clone(): void
    {}

    /**
     * Run the application.
     *
     * @return void
     */
    public function run(): void
    {   
        try {
            $this->beforeRun();
            
            $this->response = $this
                ->buildMiddlewarePipeline()
                ->handleRequest();
            
            $this->getResponse()->send();

            $this->afterRun();

        } catch (\Throwable $e) {
            die('TODO: ROOT APP Handle exception: ' . $e->getMessage());
            
        }   
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * Handle the incoming HTTP request.
     *
     * @return ResponseInterface
     */
    protected function handleRequest(): ResponseInterface
    {
        $this->getPipeline()->setFallbackHandler(
            $this(NotFoundHandler::class)
        );

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
     * Get the current HTTP response.
     *
     * @return ResponseInterface
     */
    protected function getResponse(): ResponseInterface
    {
        if ($this->response === null) {
            throw new \RuntimeException('Response has not been generated yet.');
        }

        return $this->response;
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
     * Build the middleware pipeline based on configuration.
     *
     * @return static
     */
    private function buildMiddlewarePipeline(): static
    {
        $httpMiddlewares = $this->getConfig()->get(self::CONFIG_PATH_MIDDLEWARE_HTTP) ?? [];

         foreach ($httpMiddlewares as $middlewareId) {

                $this->getPipeline()->pipe(
                    $this->getMiddlewareProxy()->withId($middlewareId)
                );
         }

        return $this;
    }

    /**
     * Dispatch the after run event.
     *
     * @return static
     */
    private function beforeRun(): static
    {
        $this->getEventDispatcher()
            ->dispatch(
                $this->getBeforeRunEvent()
            );

        return $this;
    }

    /**
     * Dispatch the after run event.
     *
     * @return static
     */
    private function afterRun(): static
    {
        $this->getEventDispatcher()
            ->dispatch(
                $this->getAfterRunEvent()
            );

        return $this;
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
     * Get the middleware proxy.
     *
     * @return MiddlewareProxy
     */
    private function getMiddlewareProxy(): MiddlewareProxy
    {
        return $this->middlewareProxy;
    }

    /**
     * Get the before run event.
     *
     * @return EventInterface
     */
    protected function getBeforeRunEvent(): EventInterface
    {
        return $this->beforeRunEvent;
    }

    /**
     * Get the after run event.
     *
     * @return EventInterface
     */
    protected function getAfterRunEvent(): EventInterface
    {
        return $this->afterRunEvent;
    }
    
}