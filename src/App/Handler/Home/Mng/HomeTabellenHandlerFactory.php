<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandlerFactory;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class HomeTabellenHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomeTabellenHandler
    {
        $page = new HomeTabellenHandler();

        parent::init($page, $container);

        $dbalConnection = $container->get(Connection::class);

        $page->setDbalConnection($dbalConnection);

        return $page;
    }
}
