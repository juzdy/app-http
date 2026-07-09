<?php
namespace Juzdy\Http\Middleware\Common;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFound implements MiddlewareInterface
{

    /**
     * @param ResponseFactoryInterface $responseFactory Factory to create response instances
     */
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * Process the request and return a 404 Not Found response.
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->responseFactory->createResponse(404);
    }
}