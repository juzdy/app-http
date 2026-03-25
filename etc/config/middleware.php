<?php

return [
    'path' => [
        'ttttt' => $this("app"),
    ],
    'middleware' => [
        "global" => [
            //10 => \Juzdy\Http\Middleware\Common\ErrorHandlerMiddleware::class,
            20 => \Juzdy\Http\Middleware\Common\RateLimitMiddleware::class,
            30 => \Juzdy\Http\Middleware\Common\SecurityHeadersMiddleware::class,
            40 => \Juzdy\Http\Middleware\Common\CorsMiddleware::class,
            // "@extends" => [
            //     "../../middleware-preset.global"
            // ],
        ],
    ],

    "middleware-preset" => [
        "global" => [
            // \Juzdy\Http\Middleware\Common\ErrorHandlerMiddleware::class,
            // \Juzdy\Http\Middleware\Common\RateLimitMiddleware::class,
            // \Juzdy\Http\Middleware\Common\SessionMiddleware::class,
            // \Juzdy\Http\Middleware\Common\SecurityHeadersMiddleware::class,
            // \Juzdy\Http\Middleware\Common\CorsMiddleware::class,
            //999 => \Juzdy\Http\Router\DynaRouter::class,
        ],
    ],
];
