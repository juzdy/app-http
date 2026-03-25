<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Http\Middleware\Proxy;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Juzdy\Http\Middleware\Exception\MiddlewareException;
use Juzdy\Http\Middleware\Proxy\Event\AfterProcessEvent;
use Juzdy\Http\Middleware\Proxy\Event\BeforeProcessEvent;

class MiddlewareProxy implements MiddlewareInterface
{
    /**
     * @var string The service identifier for the middleware to be resolved from the container
     */
    private string $middlewareId = '';

    public function __construct(
        private MiddlewareFactory $middlewareFactory,
        private BeforeProcessEvent $beforeProcessEvent,
        private AfterProcessEvent $afterProcessEvent,
    ) 
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
     * Get the middleware factory.
     *
     * @return MiddlewareFactory The middleware factory instance
     */
    private function getMiddlewareFactory(): MiddlewareFactory
    {
        return $this->middlewareFactory;
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
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * 
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middlewareId = $this->getId();

        $middleware = $this->getMiddlewareFactory()->create($middlewareId);

        $this->before([
            'middlewareId' => $middlewareId,
            'middleware' => $middleware,
            'request' => $request,
        ]);

        if (!$middleware instanceof MiddlewareInterface) {
            throw new MiddlewareException("Middleware with ID '{$middlewareId}' is not valid.");
        }

        $response = $middleware->process($request, $handler);

        $this->after([
            'middlewareId' => $middlewareId,
            'middleware' => $middleware,
            'request' => $request,
            'response' => $response,
        ]);

        return $response;
    }

    /**
     * Dispatch the before process event.
     *
     * @param array $data The data to be passed with the event
     */
    private function before(array $data): void
    {
        $this->beforeProcessEvent->fire($data);
    }

    /**
     * Dispatch the after process event.
     *
     * @param array $data The data to be passed with the event
     */
    private function after(array $data): void
    {
        $this->afterProcessEvent->fire($data);
    }
}