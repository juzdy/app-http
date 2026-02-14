<?php
namespace Juzdy\Http;

use Juzdy\App\AppInterface;
use Juzdy\Container\Attribute\Shared;

#[Shared]
class Bootstrap extends \Juzdy\App\Bootstrap
{
    public function app(): AppInterface
    {
        return $this->app ??= $this->container()->get(Http::class);
    }

}