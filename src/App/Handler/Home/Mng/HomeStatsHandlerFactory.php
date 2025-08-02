<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandlerFactory;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class HomeStatsHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomeStatsHandler
    {
        $page = new HomeStatsHandler();

        parent::init($page, $container);

        $dbalConnection = $container->get(Connection::class);

        $page->setDbalConnection($dbalConnection);

        return $page;
    }
}
