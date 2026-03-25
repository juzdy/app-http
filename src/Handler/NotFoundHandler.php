<?php 
namespace Juzdy\Http\Handler;

use Juzdy\Debug\Debug;
use Juzdy\Http\Handler;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\ResponseInterface;

class NotFoundHandler extends Handler
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        echo Debug::dumpTrace();
        die('not found handler is handling the request...');
        return $this->response()
            ->status(404)
            // ->header('Content-Type', 'text/plain')
            // ->body('404 Not Found')
            ;
    }
}