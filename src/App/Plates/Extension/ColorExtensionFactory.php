<?php

declare(strict_types=1);

namespace App\Plates\Extension;

use Mezzio\Session\SessionInterface;
use Psr\Container\ContainerInterface;

class ColorExtensionFactory
{
    public function __invoke(ContainerInterface $container): ColorExtension
    {
        return new ColorExtension();
    }
}
