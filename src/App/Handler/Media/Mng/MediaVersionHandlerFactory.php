<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Form\Search\MngMediaSearchForm;
use App\Model\Media\MediaRepositoryInterface;
use App\Handler\AbstractBaseHandlerFactory;
use Psr\Container\ContainerInterface;

class MediaVersionHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): MediaVersionHandler
    {
        $page = new MediaVersionHandler();

        // repository
        $page->setMediaRepository($container->get(MediaRepositoryInterface::class));

        parent::init($page, $container);

        return $page;
    }
}
