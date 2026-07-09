<?php

namespace Juzdy\Http;

use Juzdy\App\AppInterface;
use Juzdy\Config\ConfigInterface;
use Juzdy\App\PackageProvider\Package;
use Juzdy\Http\Middleware\MiddlewareStack;

class AppHttpPackage extends Package
{
    private array $httpMiddlewares = [];

    public function configure(ConfigInterface $config): void
    {
    }

    public function boot(AppInterface $app): void
    {
        $httpMiddlewares = $app(ConfigInterface::class)->get('middleware') ?? [];
        /**
         * Register HTTP middlewares from configuration.
         * Each middleware may be defined in the configuration with its service ID and optional priority.
         * The middlewares will be registered with the application in the order of their priority, allowing for flexible middleware stacking.
         * Example configuration:
         * {
         *   "middleware": {
         *     "SomeMiddlewareServiceId": {
         *       "priority": 100
         *     },
         *     "AnotherMiddlewareServiceId": {
         *       "priority": 200
         *     }
         *   }
         * }
         */
        foreach ($httpMiddlewares as $middlewareId => $middlewareConfig) {
            $app->withMiddleware($middlewareConfig['priority'] ?? 0, $middlewareId);
        }

        /**
         * Register a default middleware to add the X-Powered-By header to all responses.
         * This middleware is registered with a low priority to ensure it runs after all other middlewares,
         * allowing them to modify the response as needed before the header is added.
         * 
         * Just an example of how to register a simple middleware directly in the package without needing to define it as a service.
         */
        $app->withMiddleware(
            PHP_INT_MIN + 100,
            ...[
                new class implements \Psr\Http\Server\MiddlewareInterface {
                    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
                    {
                        $response = $handler->handle($request)
                            ->withHeader('X-Powered-By', 'Juzdy App HTTP');
                        return $response;
                    }
                },                
            ]
        );
    }
}
