<?php
namespace Juzdy\Http;

interface RequestInterface //extends Psr\Http\Message\ServerRequestInterface
{
    /**
     * Magic method to get a value from the request.
     * The sequence of checking is: server, get, post, session, cookie.
     * 
     * @param string $key The key to retrieve
     * 
     * @return mixed The value associated with the key
     */
    public function __invoke(string $key): mixed;

    /**
     * Get the request method.
     * 
     * @return string
     */
    public function method(): string;

    /**
     * Check if the request method is POST.
     * 
     * @return bool
     */
    public function isPost(): bool;

    /**
     * Check if the request method is GET.
     * 
     * @return bool
     */
    public function isGet(): bool;

    /**
     * Get a specific header value.
     * 
     * @param string $key The header name
     * 
     * @return string|null The header value or null if not found
     */
    public function header(string $key): ?string;

    /**
     * Get a value from the request (checks in order: server, get, post, session, cookie).
     * 
     * @param string $key
     * @return mixed
     */
    public function request(string $key): mixed;

    /**
     * @deprecated
     * Alias for request method to get a value from the request.
     * 
     * @param string $key
     * @return mixed
     */
    public function getRequest(string $key) : mixed;

    /**
     * Get or set a value from $_SERVER.
     * if $key is null, returns the entire $_SERVER array.
     * 
     * @param string|null $key  The server variable name
     * @param mixed $value      The value to set (if null, acts as a getter)
     * 
     * @return mixed            The server variable value when acting as a getter, 
     *                          or the entire server array if $key is null
     */
    public function server(?string $key = null, mixed $value = null): mixed;

    /**
     * Get or set a value from $_GET or all query parameters.  Or get all query parameters if $key is null.
     * 
     * @param string|null $key  The query parameter key
     * @param mixed $value      The value to set (if null, acts as a getter)
     * 
     * @return mixed            The query parameter value when acting as a getter,
     *                          or the entire query parameters array if $key is null
     */
    public function query(?string $key = null, mixed $value = null): mixed;

    /**
     * 
     * 
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function post(string $key, mixed $value = null): mixed;

    /**
     * Get or set a value in $_SESSION. Or get all session data if $key is null.
     * 
     * @param string|null $key  The session key
     * @param mixed $value      The value to set (if null, acts as a getter)
     * 
     * @return mixed            The session value when acting as a getter,
     *                          or the entire session array if $key is null
     */
    public function session(?string $key = null, mixed $value = null): mixed;
    /**
     * Cleans the session data.
     * @return void
     */
    public function clearSession(): void;

    /**
     * Get or set a value from $_COOKIE. Or get all cookies if $key is null.
     * 
     * @param string|null $key  The cookie name
     * @param mixed $value      The value to set (if null, acts as a getter)
     * 
     * @return mixed            The cookie value when acting as a getter,
     *                          or the entire cookies array if $key is null
     */
    public function cookie(?string $key = null, mixed $value = null): mixed;
    

    /**
     * Get all uploaded files from $_FILES.
     * 
     * @param string|null $key  The specific file input name (if null, returns all files)
     * 
     * @return array            The uploaded files array or specific file info
     */
    public function files(?string $key = null): array;

    /**
     * Get a value from $_FILES or all files.
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function file(?string $key = null, mixed $default = null): mixed;
    
}