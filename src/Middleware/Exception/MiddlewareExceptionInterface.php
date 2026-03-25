<?php
namespace Juzdy\Http\Middleware\Exception;

use Throwable;

interface MiddlewareExceptionInterface extends Throwable
{
    // Marker interface for exceptions thrown by middleware components
}