<?php

return [
    'middleware' => [

        // \Juzdy\Http\Middleware\Common\ErrorHandlerMiddleware::class => [
        //     'priority' => 10,
        // ],
        \Juzdy\Http\Middleware\Common\ResponseFlusher::class => [
            'priority' => PHP_INT_MIN + 100,
        ],
        \Juzdy\Http\Middleware\Common\NotFound::class => [
            'priority' => PHP_INT_MAX - 100,
        ],
    ],
];
