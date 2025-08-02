<?php

declare(strict_types=1);

namespace App\Plates\Extension;

use Psr\Container\ContainerInterface;

class UrlpoolExtensionFactory
{
    public function __invoke(ContainerInterface $container): UrlpoolExtension
    {
        return new UrlpoolExtension();
    }
}
