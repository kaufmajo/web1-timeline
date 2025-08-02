<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\UrlpoolService;
use Psr\Container\ContainerInterface;

class UrlpoolMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): UrlpoolMiddleware
    {
        $middleware = new UrlpoolMiddleware();

        $middleware->setUrlpoolService($container->get(UrlpoolService::class));

        return $middleware;
    }
}
