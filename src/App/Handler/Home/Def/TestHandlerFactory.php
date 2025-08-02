<?php

declare(strict_types=1);

namespace App\Handler\Home\Def;

use App\Handler\AbstractBaseHandlerFactory;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class TestHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): TestHandler
    {
        $handler = new TestHandler();

        $handler->setDbalConnection($container->get(Connection::class));

        return $handler;
    }
}
