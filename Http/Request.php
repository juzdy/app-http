<?php
namespace Juzdy\Http;

/**
 * The simple HTTP Request class.
 * 
 * HTTP request data (GET, POST, SESSION, etc).
 */
class Request implements RequestInterface
{

    /**
     * @var array
     */
    private ?array $session = null;
    /**
     * @var array
     */
    private array $cookies = [];
    /**
     * @var array
     */
    private array $server = [];
    /**
     * @var array
     */
    private array $params = [];
    /**
     * @var array
     */
    private array $queryParams = [];
    /**
     * @var array
     */
    private array $postParams = [];
    /**
     * @var array
     */
    private array $files = [];
    /**
     * @var array
     */
    private array $filesRaw = [];


    /**
     * Request constructor.
     * Initializes request data from PHP superglobals.
     */
    public function __construct()
    {
        $this->session = null; //lazy load
        $this->params = &$_REQUEST;
        $this->cookies = &$_COOKIE;
        $this->server = &$_SERVER;
        $this->queryParams = &$_GET;
        $this->postParams = &$_POST;
        $this->filesRaw = &$_FILES;
        $this->files = $this->normalizeFiles($this->filesRaw);
    }

    /**
     *{@inheritdoc}
     */
    public function __invoke(string $key): mixed
    {
        return $this->request($key);
    }

    /**
     *{@inheritdoc}
     */
    public function method(): string
    {
        return $this->server('REQUEST_METHOD');
    }

     /**
     *{@inheritdoc}
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     *{@inheritdoc}
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     *{@inheritdoc}
     */
    public function header(string $key): ?string
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for servers without getallheaders()
            $headers = [];
            foreach ($this->server() as $name => $value) {
                if (str_starts_with($name, 'HTTP_')) {
                    $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$headerName] = $value;
                }
            }
        }

        return $headers[$key] ?? null;
    }

    /**
     *{@inheritdoc}
     */
    public function request(string $key): mixed
    {
        return $this->server($key) ?? $this->query($key) ?? $this->post($key) ?? $this->session($key) ?? $this->cookie($key) ?? null;
    }

    /**
     *{@inheritdoc}
     */
    public function getRequest(string $key): mixed
    {
        return $this->request($key);
    }

    /**
     *{@inheritdoc}
     */
    public function query(?string $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }

        return $value ? $this->queryParams[$key] = $value : ($this->queryParams[$key] ?? null);
    }

    /**
     *{@inheritdoc}
     */
    public function post(?string $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->postParams;
        }

        return $value ? $this->postParams[$key] = $value : ($this->postParams[$key] ?? null);
    }

    /**
     *{@inheritdoc}
     */
    public function session(?string $key = null, mixed $value = null): mixed
    {
        if ($this->session === null) {
            $this->session = &$_SESSION; //lazy load
        }

        if ($key === null) {
            return $this->session;
        }

        return $value ? $this->session[$key] = $value : ($this->session[$key] ?? null);
    }

    /**
     *{@inheritdoc}
     */
    public function clearSession(): void
    {
        $this->session = [];
        $_SESSION = []; //fallback if not enough
    }

    /**
     *{@inheritdoc}
     */
    public function cookie(?string $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->cookies;
        }

        return $value ? $this->cookies[$key] = $value : ($this->cookies[$key] ?? null);
    }

    /**
     *{@inheritdoc}
     */
    public function server(?string $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->server;
        }

        return $value ? $this->server[$key] = $value : ($this->server[$key] ?? null);
    }

    /**
     *{@inheritdoc}
     */
    public function files(?string $key = null): array
    {
        if ($key === null) {
            return $this->files;
        }

        return $this->files[$key] ?? [];
    }

    /**
     *{@inheritdoc}
     */
    public function file(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }

        return $this->files[$key] ?? $default;
    }

    /**
     * Normalize the $_FILES array structure.
     * 
     * @param array $files
     * @return array
     */
    protected function normalizeFiles(array $files): array
    {
        foreach ($files as $key => $fileData) {
            $files[$key] = $this->normalizeFileArray($fileData);
        }

            return $files;
    }

    /**
     * Normalize a single file array structure.
     * 
     * @param array $files
     * @return array
     */
    protected function normalizeFileArray(array $files): array
    {
        $normalized = [];

        if (is_array($files['name']) && count($files['name']) > 0 && is_array($files['name'])) {
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $normalized[] = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
            }
        } else {
            $normalized[] = [
                'name'     => $files['name'],
                'type'     => $files['type'],
                'tmp_name' => $files['tmp_name'],
                'error'    => $files['error'],
                'size'     => $files['size'],
            ];
        }

        return $normalized;
    }


    

    
}