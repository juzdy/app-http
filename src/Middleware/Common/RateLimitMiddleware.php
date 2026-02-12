<?php
namespace Juzdy\Http\Middleware\Common;

use Juzdy\Http\HandlerInterface;
use Juzdy\Http\Middleware\MiddlewareInterface;
use Juzdy\Http\Middleware\RequestHandlerInterface;
use Juzdy\Http\RequestInterface;
use Juzdy\Http\Response;
use Juzdy\Http\ResponseInterface;
use Juzdy\Request;

/**
 * Rate Limiting Middleware
 * 
 * Prevents excessive requests from the same IP address.
 * This is an example middleware that can be applied to specific controllers.
 * 
 * Note: This implementation uses session storage for simplicity. 
 * For production use with IP-based rate limiting across sessions, 
 * consider using a proper cache backend (Redis, Memcached) or file-based storage.
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * Maximum requests per time window
     */
    private int $maxRequests;

    /**
     * Time window in seconds
     */
    private int $timeWindow;

    /**
     * Constructor
     *
     * @param int $maxRequests Maximum requests allowed in the time window
     * @param int $timeWindow Time window in seconds
     */
    public function __construct(int $maxRequests = 60, int $timeWindow = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }

    /**
     * Process the request.
     *
     * @param RequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        $ip = $request->server('REMOTE_ADDR') ?? 'unknown';
        
        // Note: Using session storage here for simplicity
        // For production, use shared cache (Redis/Memcached) for IP-based rate limiting
        $key = 'rate_limit_' . md5($ip);
        
        // Get current request data from session
        $rateLimitData = $request->session($key, [
            'count' => 0,
            'reset_time' => time() + $this->timeWindow,
        ]);
        
        // Reset if time window has passed
        if (time() > $rateLimitData['reset_time']) {
            $rateLimitData = [
                'count' => 0,
                'reset_time' => time() + $this->timeWindow,
            ];
        }
        
        // Increment request count
        $rateLimitData['count']++;
        
        // Check if limit exceeded
        if ($rateLimitData['count'] > $this->maxRequests) {
            http_response_code(429);
            header('Retry-After: ' . ($rateLimitData['reset_time'] - time()));
            echo json_encode([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => $rateLimitData['reset_time'] - time(),
            ]);
            exit;
        }
        
        // Save updated count
        $request->session($key, $rateLimitData);
        
        // Add rate limit headers
        header('X-RateLimit-Limit: ' . $this->maxRequests);
        header('X-RateLimit-Remaining: ' . ($this->maxRequests - $rateLimitData['count']));
        header('X-RateLimit-Reset: ' . $rateLimitData['reset_time']);
        
        // Continue to next middleware or handler
        return $handler->handle($request);
    }
}
