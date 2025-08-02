<?php

declare(strict_types=1);

namespace App\Handler\Termin\Def;

use App\Handler\AbstractBaseHandlerFactory;
use App\Model\Termin\TerminRepositoryInterface;
use Psr\Container\ContainerInterface;

class TerminShowHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): TerminShowHandler
    {
        $handler = new TerminShowHandler();

        // repository
        $handler->setTerminRepository($container->get(TerminRepositoryInterface::class));

        parent::init($handler, $container);

        return $handler;
    }
}
