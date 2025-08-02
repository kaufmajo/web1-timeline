<?php

declare(strict_types=1);

namespace App\Handler\Termin\Mng;

use App\Form\Search\DefTerminSearchForm;
use App\Handler\AbstractBaseHandlerFactory;
use App\Model\Termin\TerminRepositoryInterface;
use Psr\Container\ContainerInterface;

class TerminSearchHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): TerminSearchHandler
    {
        $page = new TerminSearchHandler();

        // repository
        $page->setTerminRepository($container->get(TerminRepositoryInterface::class));

        // form
        $formManager = $container->get('FormElementManager');
        $page->setForm('def-termin-search-form', $formManager->get(DefTerminSearchForm::class));

        parent::init($page, $container);

        return $page;
    }
}
