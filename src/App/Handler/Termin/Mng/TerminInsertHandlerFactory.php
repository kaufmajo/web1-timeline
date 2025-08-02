<?php

declare(strict_types=1);

namespace App\Handler\Termin\Mng;

use App\Form\TerminForm;
use App\Handler\AbstractBaseHandlerFactory;
use App\Model\Media\MediaCommandInterface;
use App\Model\Termin\TerminCommandInterface;
use App\Model\Termin\TerminRepositoryInterface;
use Psr\Container\ContainerInterface;

class TerminInsertHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): TerminInsertHandler
    {
        $page = new TerminInsertHandler();

        // command
        $page->setMediaCommand($container->get(MediaCommandInterface::class));
        $page->setTerminCommand($container->get(TerminCommandInterface::class));

        // repository
        $page->setTerminRepository($container->get(TerminRepositoryInterface::class));

        // form
        $formManager = $container->get('FormElementManager');
        $page->setForm('termin-form', $formManager->get(TerminForm::class));

        parent::init($page, $container);

        return $page;
    }
}
