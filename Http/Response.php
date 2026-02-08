<?php
namespace Juzdy\Http;

class Response implements ResponseInterface
{
    protected int $statusCode = 200;
    protected array $headers = [];
    protected  $body = null;

    public function __construct()
    {
    }
    
    protected function init(): void
    {
        
        // Initialization code can be added here if needed
    }

    public function reset(): static
    {
        $this->statusCode = 200;
        $this->headers = [];
       
        if ($this->body !== null) {
            fclose($this->body);
        }
        $this->body = fopen('php://memory', 'r+');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function status(?int $code = null): int|static
    {
        if ($code !== null) {
            $this->statusCode = $code;
            
            return $this;
        }
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function header(string $name, ?string $value = null): null|string|static
    {
        if ($value !== null) {
            $this->headers[$name] = $value;

            return $this;
        }

        return $this->headers[$name] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function body(?string $content = null): string|static
    {
        $this->body ??= fopen('php://memory', 'r+');
        if ($content !== null) {
            fwrite($this->body, $content);

            return $this;
        }
        rewind($this->body);
        return stream_get_contents($this->body);
    }

    /**
     * {@inheritDoc}
     */
    public function send(): static
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        if ($this->body !== null) {
            rewind($this->body);
            fpassthru($this->body);
        }

        return $this;
    }

    public function redirect(string $route, array $args = []): static
    {
        if (filter_var($route, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Use relative paths for redirects: $route");
        }

        if (!empty($args)) {
            $route .= (strpos($route, '?') === false ? '?' : '&') . http_build_query($args);
        }

        // Prevent header injection by removing newlines
        $route = preg_replace('/[\r\n]/', '', $route);

        return $this
            ->reset()
            ->status(302)
            ->header('Location', $route);
    }
}