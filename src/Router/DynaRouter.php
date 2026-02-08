<?php
namespace Juzdy\Http\Router;

use Psr\Container\ContainerInterface;
use Juzdy\Config;
use Juzdy\Container\Attribute\Preference;
use Juzdy\Container\Container;
use Juzdy\Http\Exception\NotFoundException;
use Juzdy\Http\HandlerInterface;
use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\MiddlewareAwareInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

#[Preference(preferences: [ContainerInterface::class => Container::class])]
class DynaRouter implements RouterInterface
{

    public function __construct(protected ContainerInterface $container)
    {
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public static function route(string $handlerClass): string
    {
        //$class = str_replace(, '', $handlerClass);

        $parts = preg_split('/(?=[A-Z])/', $handlerClass, -1, PREG_SPLIT_NO_EMPTY);
        $routeParts = [];
        foreach ($parts as $part) {
            $routeParts[] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $part));
        }
        return implode('/', $routeParts);
    }

    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->dispatch($request);
        } catch (NotFoundException $e) {
            // Let the next middleware/handler process the request
        }
        
        return $handler->handle($request);
    }

    /**
     * Dispatch a route to the appropriate controller/action.
     * e.g. 'account/profile' -> \App\Controller\Account\ProfileController
     */
    private function dispatch(RequestInterface $request): ResponseInterface
    {
        $route = $request->query(Config::get('http.htaccess_handler_rewrite_param') ?? uniqid()) ?? '';

        $parts = array_filter(explode('/', $route));

        if (count($parts) < 2) {
            $parts[] = Config::get('http.default_handler') ?? 'index'; // Default handler if not specified
        }
        
        // Convert kebab-case to camelCase for each part
        $parts = array_map(function($part) {
            return preg_replace_callback('/-([a-z])/', function($matches) {
                return strtoupper($matches[1]);
            }, $part);
        }, $parts);
        
        // Build the fully qualified class name
        $route = implode('\\', array_map('ucfirst', $parts));
        // Remove underscores for class names
        $route = str_replace('_', '', $route); 

        $composerNamespaces = \Juzdy\Composer\Composer::namespaces();

        $handlerClasses = [];

        foreach ($composerNamespaces as $namespace) {
            foreach (Config::get('http.request_handlers_namespace') as $handlerNamespace) {
                $possibleHandler = preg_replace(
                    '/\\\\+/',
                    '\\',
                    str_replace('{namespace}', $namespace, $handlerNamespace) . '\\' . $route
                );
                if (class_exists($possibleHandler) && is_subclass_of($possibleHandler, HandlerInterface::class)) {
                    $handlerClasses[] = $possibleHandler;
                }
            }
        }

        if (count($handlerClasses) === 0) {
                throw new NotFoundException("Handler not found for route: $route");
        }

        if (count($handlerClasses) > 1) {
                $handlers = implode(', ', $handlerClasses);
                throw new \RuntimeException("Multiple handlers found for route: $route: $handlers");
        }

        $handlerClass = $handlerClasses[0];

        if (!class_exists($handlerClass) || !is_subclass_of($handlerClass, HandlerInterface::class)) {
                throw new \RuntimeException("Handler not found: $handlerClass");
        }

        // Instantiate the handler via the container
        $handler = $this->getContainer()->get($handlerClass);
        
        // Apply middleware group to controller if applicable
        $this->applyMiddlewareGroup($handler);
        
        // Execute controller with its middleware
        return $handler->executeWithMiddleware(function() use ($handler, $request) {
            return $handler->handle($request);
        }, $request);
    }

    /**
     * Apply middleware group to a controller.
     *
     * @param HandlerInterface $handler
     * @return void
     */
    private function applyMiddlewareGroup(HandlerInterface $handler): void
    {
        $groups = array_merge(
            [$handler::class],
            class_parents($handler),
            class_implements($handler),
        );

        foreach ($groups as $group) {
            
            $middlewareClasses = Config::get("middleware.groups.{$group}") ?? [];
            
            foreach ($middlewareClasses as $middlewareClass) {
                if (class_exists($middlewareClass)) {
                    if (!is_subclass_of($middlewareClass, MiddlewareInterface::class)) {
                        throw new \RuntimeException("Middleware class {$middlewareClass} must implement MiddlewareInterface.");
                    }

                    if ($handler instanceof MiddlewareAwareInterface) {
                        $handler->addMiddleware(
                            $this->getContainer()->get($middlewareClass)
                        );
                    } else {
                         throw new \RuntimeException("Handler class " . get_class($handler) . " must implement MiddlewareAwareInterface to add middleware.");
                    }
                }
            }
        }
    }

}
