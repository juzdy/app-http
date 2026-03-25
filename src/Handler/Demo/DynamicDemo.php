<?php
namespace Juzdy\Http\Handler\Demo;


use Juzdy\Http\Handler;
use Juzdy\Http\Handler\Demo\Contract\WithDemoMiddleware;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

/**
 * Demo Index Handler
 */
class DynamicDemo extends Handler implements WithDemoMiddleware
{

    public function __construct(
        // Inject dependencies here if needed
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->response()
            ->header('Content-Type', 'text/plain')
            ->body("\n" . static::class. " is handling the request!");
    }
}