<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Http;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Juzdy\App\AppInterface;
use Juzdy\Config\ConfigInterface;
use Juzdy\EventBus\Event\EventInterface;
use Juzdy\Http\Event\AfterRun;
use Juzdy\Http\Event\BeforeRun;
use Juzdy\Container\Attribute\Parameter\Using;
use Juzdy\Container\Attribute\Prefer;
use Juzdy\Container\Attribute\Shared;
use Juzdy\Debug\Debug;
use Juzdy\Http\Middleware\MiddlewareStack;
use Juzdy\Http\Middleware\Proxy\MiddlewareProxyFactory;

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
    const CONFIG_PATH_MIDDLEWARE_GLOBAL = 'middleware.global';

    private ?ResponseInterface $response = null;
    private ?ServerRequestInterface $request = null;

    
    public function __construct(
        private ConfigInterface $config,
        private ContainerInterface $container,
        #[Using(BeforeRun::class)]
        private EventInterface $beforeRunEvent,
        #[Using(AfterRun::class)]
        private EventInterface $afterRunEvent,
    ) {
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
            //$this->beforeRun();
            
            $this->response = $this
                ->buildMiddlewareStack()
                ->handleRequest();
            
            //$this->getResponse()->send();

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
     * Get the dependency injection container.
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Handle the incoming HTTP request.
     *
     * @return ResponseInterface
     */
    protected function handleRequest(): ResponseInterface
    {
        // $this->getMiddlewareStack()->setFallbackHandler(
        //     $this(NotFoundHandler::class)
        // );

        return $this->getMiddlewareStack()
            ->handle($this->getRequest());
    }

    

    /**
     * Get the current HTTP request.
     *
     * @return ServerRequestInterface
     */
    protected function getRequest(): ServerRequestInterface
    {
        return $this->request ??= $this->getRequestFactory()->createServerRequest();
    }

    protected function getRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->getContainer()->get(ServerRequestFactoryInterface::class);
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
     * @return MiddlewareStack
     */
    protected function getMiddlewareStack(): MiddlewareStack
    {
        return $this->getContainer()->get(MiddlewareStack::class);
    }

    /**
     * Build the middleware stack based on configuration.
     *
     * @return static
     */
    private function buildMiddlewareStack(): static
    {
        $httpMiddlewares = $this->getConfig()->get(self::CONFIG_PATH_MIDDLEWARE_GLOBAL) ?? [];
        ksort($httpMiddlewares);
         foreach ($httpMiddlewares as $priority => $middlewareId) {

                $this->getMiddlewareStack()->push(
                    $this->getMiddlewareProxyFactory()->create($middlewareId),
                    $priority
                );
         }
Debug::dd($this->getConfig()->all());
        return $this;
    }

    /**
     * Dispatch the after run event.
     *
     * @return static
     */
    private function beforeRun(): static
    {
        $this->getBeforeRunEvent()->fire(
                [
                    'app' => $this,
                    'request' => $this->getRequest(),
                ]
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
        $this->getAfterRunEvent()->fire(
                [
                    'app'     => $this,
                    'request' => $this->getRequest(),
                    'response' => $this->getResponse(),
                ]
            );

        return $this;
    }

    /**
     * Get the middleware proxy.
     *
     * @return MiddlewareProxyFactory
     */
    private function getMiddlewareProxyFactory(): MiddlewareProxyFactory
    {
        return $this->getContainer()->get(MiddlewareProxyFactory::class);
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