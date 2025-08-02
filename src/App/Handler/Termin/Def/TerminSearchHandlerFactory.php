<?php

declare(strict_types=1);

namespace App\Handler\Termin\Def;

use App\Form\Search\DefTerminSearchForm;
use App\Handler\AbstractBaseHandlerFactory;
use App\Model\Termin\TerminRepositoryInterface;
use Psr\Container\ContainerInterface;

class TerminSearchHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): TerminSearchHandler
    {
        $handler = new TerminSearchHandler();

        // repository
        $handler->setTerminRepository($container->get(TerminRepositoryInterface::class));

        // form
        $formManager = $container->get('FormElementManager');
        $handler->setForm('def-termin-search-form', $formManager->get(DefTerminSearchForm::class));

        parent::init($handler, $container);

        return $handler;
    }
}
