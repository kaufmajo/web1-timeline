<?php

declare(strict_types=1);

namespace App\Handler\Media\Mng;

use App\Form\Search\MngMediaSearchForm;
use App\Model\Media\MediaRepositoryInterface;
use App\Handler\AbstractBaseHandlerFactory;
use Psr\Container\ContainerInterface;

class MediaReadHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): MediaReadHandler
    {
        $page = new MediaReadHandler();

        // repository
        $page->setMediaRepository($container->get(MediaRepositoryInterface::class));

        // form
        $formManager = $container->get('FormElementManager');
        $page->setForm('media-mng-search-form', $formManager->get(MngMediaSearchForm::class));

        parent::init($page, $container);

        return $page;
    }
}
