<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Model\Media\MediaCommandInterface;
use App\Model\Media\MediaRepositoryInterface;
use App\Handler\AbstractBaseHandlerFactory;
use Psr\Container\ContainerInterface;

class MediaDeleteHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): MediaDeleteHandler
    {
        $page = new MediaDeleteHandler();

        // command
        $page->setMediaCommand($container->get(MediaCommandInterface::class));

        // repository
        $page->setMediaRepository($container->get(MediaRepositoryInterface::class));

        parent::init($page, $container);

        return $page;
    }
}
