<?php

declare(strict_types=1);

namespace App\Handler\Auth\Def;

use App\Handler\AbstractBaseHandlerFactory;
use App\Service\HistoryService;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class LoginHandlerFactory extends AbstractBaseHandlerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        $handler = new LoginHandler($container->get(AuthenticationInterface::class));

        // historyService
        $handler->setHistoryService($container->get(HistoryService::class));

        parent::init($handler, $container);

        return $handler;
    }
}
