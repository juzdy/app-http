<?php 
namespace Juzdy\Http\Handler;

use Juzdy\Http\Handler;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

class NotFoundHandler extends Handler
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->response()
            ->status(404)
            // ->header('Content-Type', 'text/plain')
            // ->body('404 Not Found')
            ;
    }
}