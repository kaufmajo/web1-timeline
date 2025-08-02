<?php

declare(strict_types=1);

namespace App\Handler\Home\Def;

use App\Handler\AbstractBaseHandlerFactory;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class CleanupHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): CleanupHandler
    {
        $handler = new CleanupHandler();

        // db adapter
        $handler->setDbalConnection($container->get(Connection::class));

        parent::init($handler, $container);

        return $handler;
    }
}
