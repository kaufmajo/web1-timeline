<?php

declare(strict_types=1);

namespace App\Handler\Home\Mng;

use App\Handler\AbstractBaseHandlerFactory;
use Psr\Container\ContainerInterface;

class HomePhpinfoHandlerFactory extends AbstractBaseHandlerFactory
{
    public function __invoke(ContainerInterface $container): HomePhpinfoHandler
    {
        $page = new HomePhpinfoHandler();

        parent::init($page, $container);

        return $page;
    }
}
