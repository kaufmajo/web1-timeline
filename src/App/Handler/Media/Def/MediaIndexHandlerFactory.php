<?php

declare(strict_types=1);

namespace App\Handler\Media\Def;

use App\Model\Media\MediaRepositoryInterface;
use Psr\Container\ContainerInterface;

class MediaIndexHandlerFactory
{
    public function __invoke(ContainerInterface $container): MediaIndexHandler
    {
        $controller = new MediaIndexHandler();

        // config
        $controller->setConfig($container->get('config'));

        // repository
        $controller->setMediaRepository($container->get(MediaRepositoryInterface::class));

        return $controller;
    }
}
