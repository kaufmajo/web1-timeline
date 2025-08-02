<?php

declare(strict_types=1);

namespace App\Handler\Termin\Def;

use App\Form\Search\DefTerminSearchInputFilter;
use App\Handler\AbstractBaseHandlerFactory;
use App\Model\Termin\TerminRepositoryInterface;
use Psr\Container\ContainerInterface;

class TerminIcsHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): TerminIcsHandler
    {
        $handler = new TerminIcsHandler(new DefTerminSearchInputFilter());

        // repository
        $handler->setTerminRepository($container->get(TerminRepositoryInterface::class));

        parent::init($handler, $container);

        return $handler;
    }
}
