<?php
namespace Juzdy\Http;

use Juzdy\Http\Handler\MiddlewareTrait;
use Juzdy\Http\Handler\RenderTrait;

abstract class Handler implements HandlerInterface, MiddlewareAwareInterface
{
    use MiddlewareTrait;
    use RenderTrait;

    /**
     * {@inheritDoc}
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        throw new \Exception('Not implemented');
    }


    /**
     * Get the route path for this handler
     *
     * @return string The route path
     */
    public static function route(): string
    {
        return Router::route(static::class);
    }

    /**
     * @var ResponseInterface|null
     */
    private ?ResponseInterface $response = null;


    /**
     * Get or create the response object.
     *
     * @return ResponseInterface
     */
    protected function response(): ResponseInterface
    {
        return $this->response ??= new Response();
    }
    
    /**
     * Redirect to a given URL
     *
     * @param string $url The URL to redirect to
     * @param array $args Query parameters to append
     * 
     * @return ResponseInterface The redirect response
     */
    public function redirect(string $route, array $args = []): ResponseInterface
    {
        if (filter_var($route, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Use relative paths for redirects: $route");
        }

        if (!empty($args)) {
            $route .= (strpos($route, '?') === false ? '?' : '&') . http_build_query($args);
        }

        // Prevent header injection by removing newlines
        $route = preg_replace('/[\r\n]/', '', $route);

        return $this->response()
            ->reset()
            ->status(302)
            ->header('Location', $route);
    }

    /**
     * Redirect to the referer URL
     * Only paths are allowed for security reasons.
     *
     * @param RequestInterface $request The request object
     * 
     * @return ResponseInterface The redirect response
     */
    protected function redirectReferer(RequestInterface $request): ResponseInterface
    {
        $referer = $request->header('Referer') ?? '/';
        $referer = preg_replace('#^[a-zA-Z][a-zA-Z0-9+.-]*://[^/]*#', '', $referer);

        return $this->redirect($referer);
    }

}
