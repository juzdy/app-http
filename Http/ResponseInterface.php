<?php
namespace Juzdy\Http;

interface ResponseInterface
{
    /**
     * Reset the response to its initial state.
     * 
     * @return static The Response instance
     */
    public function reset(): static;

    /**
     * Get or set the HTTP status code.
     *
     * @param int|null $code The HTTP status code to set, or null to get the current code
     * 
     * @return int|static The current HTTP status code when acting as a getter, or the Response instance when acting as a setter
     */
    public function status(?int $code = null): int|static;

    /**
     * Get or set an HTTP header.
     *
     * @param string $name The name of the header
     * @param string|null $value The value to set for the header, or null to get the current value
     * 
     * @return null|string|static The current value of the header, when acting as a getter, or the Response instance when acting as a setter
     */
    public function header(string $name, ?string $value = null): null|string|static;

    /**
     * Get or set the body content.
     *
     * @param string|null $content The content to set, or null to get the current content
     * 
     * @return string|static The current body content when acting as a getter, or the Response instance when acting as a setter
     */
    public function body(?string $content = null): string|static;

    /**
     * Send the response to the client.
     * 
     * @return static The Response instance
     */
    public function send(): static;
}