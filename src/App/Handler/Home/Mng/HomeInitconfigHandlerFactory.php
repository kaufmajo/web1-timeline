<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandlerFactory;
use Psr\Container\ContainerInterface;

class HomeInitconfigHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomeInitconfigHandler
    {
        $page = new HomeInitconfigHandler();

        parent::init($page, $container);

        return $page;
    }
}
