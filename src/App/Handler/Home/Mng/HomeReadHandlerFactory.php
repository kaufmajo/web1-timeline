<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandlerFactory;
use Psr\Container\ContainerInterface;

class HomeReadHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomeReadHandler
    {
        $page = new HomeReadHandler();

        parent::init($page, $container);

        return $page;
    }
}
