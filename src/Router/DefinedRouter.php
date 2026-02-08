<?php
namespace Juzdy\Http\Router;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;
use Juzdy\Http\RouterInterface;

class DefinedRouter implements RouterInterface
{
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        return $this->dispatch($request);
    }

    protected function dispatch(RequestInterface $request): ResponseInterface
    {
        
    }
}