<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Form\MediaForm;
use App\Model\Media\MediaCommandInterface;
use App\Model\Media\MediaRepositoryInterface;
use App\Handler\AbstractBaseHandlerFactory;
use Psr\Container\ContainerInterface;

class MediaInsertHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): MediaInsertHandler
    {
        $page = new MediaInsertHandler();

        // command
        $page->setMediaCommand($container->get(MediaCommandInterface::class));

        // repository
        $page->setMediaRepository($container->get(MediaRepositoryInterface::class));

        // form
        $formManager = $container->get('FormElementManager');
        $page->setForm('media-form', $formManager->get(MediaForm::class));

        parent::init($page, $container);

        return $page;
    }
}
