<?php

namespace App\Factory;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container): Logger
    {
        // Erstelle einen Logger
        $logger = new Logger('app-logger');

        // Füge einen StreamHandler hinzu, der Logs an stderr ausgibt (kann angepasst werden)
        $logger->pushHandler(new StreamHandler(getcwd() . '/data/log/app.txt', Level::Debug));

        return $logger;
    }
}
