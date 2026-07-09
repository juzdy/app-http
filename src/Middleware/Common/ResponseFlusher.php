<?php
namespace Juzdy\Http\Middleware\Common;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseFlusher implements MiddlewareInterface
{

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        $this->flush($response);

        return $response;
    }

    /**
     * Flush the response to the client.
     *
     * @param ResponseInterface $response The response to flush
     */
    public function flush(ResponseInterface $response): void
    {        
        $response = $response->hasHeader('Content-Length') ? $response : $response->withHeader('Content-Length', (string)$response->getBody()->getSize());
        
        // Send status line
        header(sprintf('HTTP/%s %d %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()), true, $response->getStatusCode());
        
        // Send headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        //echo $response->getBody();

        $outputStream = fopen('php://output', 'wb');

        if ($response->getBody()->isSeekable()) {
            $response->getBody()->rewind();
        }
        while (!$response->getBody()->eof()) {
            $chunk = $response->getBody()->read(8192);
            fwrite($outputStream, $chunk);
            flush();
        }
        fclose($outputStream);

    }
}