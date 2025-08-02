<?php

declare(strict_types=1);

namespace App\Handler\Termin\Mng;

use App\Handler\AbstractBaseHandlerFactory;
use App\Model\Termin\TerminRepositoryInterface;
use Psr\Container\ContainerInterface;

class TerminCalendarHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): TerminCalendarHandler
    {
        $page = new TerminCalendarHandler();

        // repository
        $page->setTerminRepository($container->get(TerminRepositoryInterface::class));

        parent::init($page, $container);

        return $page;
    }
}
