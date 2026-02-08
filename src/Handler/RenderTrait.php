<?php

namespace Juzdy\Http\Handler;

use Juzdy\Http\ResponseInterface;
use Juzdy\Layout\Layout;
use Juzdy\Layout\LayoutInterface;

trait RenderTrait
{
    protected ?LayoutInterface $layout = null;
    
    protected function getLayout(): LayoutInterface
    {
        return $this->layout ??= new Layout();
    }

    protected function template(string $template, array $context = []): ResponseInterface
    { 
        $layoutObj = $this->getLayout()
            ->template($template)
            ->context($context/*, true*/);
        
        return $this->response()
                ->body(
                    $layoutObj->render($template)
                );
    }

    /**
     * Render a template without layout.
     *
     * @param string $template The template name
     * @param array $params The parameters for the view
     * 
     * @return ResponseInterface The rendered response
     */
    // protected function template(string $template, array $params = [], string $layout = ''): ResponseInterface
    // {
        
    // }

    protected function layout(string $layout, string $contentTemplate, array $params = [], ?string $index = null): ResponseInterface
    {
        $layoutObj = $this->getLayout()
            ->layout($layout)
            ->template($contentTemplate)
            ->context($params/*, true*/);

        if ($index !== null) {
            $layoutObj->index($index);
        }

        $this->response()
                ->status(200)
                ->body(
                    $layoutObj->render()
                );

        return $this->response();    }

    /**
     * Return a raw response.
     *
     * @param string $content The raw content
     * @param array $headers Additional headers to set
     * 
     * @return ResponseInterface The raw response
     */
    protected function raw(string $content, array $headers = []): ResponseInterface
    {
        $this->response()
                ->status(200)
                ->body($content);
        
        foreach ($headers as $name => $value) {
            $this->response()->header($name, $value);
        }

        return $this->response();
    }

    /**
     * Return a JSON response.
     *
     * @param mixed $data The data to be encoded as JSON
     * @param array $headers Additional headers to set
     * 
     * @return ResponseInterface The JSON response
     */
    protected function json(mixed $data, array $headers = []): ResponseInterface
    {
        $jsonContent = json_encode($data);

        $this->response()
            ->status(200)
            ->header('Content-Type', 'application/json')
            ->body($jsonContent);
        
        foreach ($headers as $name => $value) {
            $this->response()->header($name, $value);
        }

        return $this->response();
    }
}