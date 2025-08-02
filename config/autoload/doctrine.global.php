<?php

use App\Middleware\DbalLoggingMiddleware;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;

return [
    'doctrine' => [
        'connection' => [
            'dbname'   => 'evangel3_egli1',
            'driver'   => 'pdo_mysql',
            // add charset, port, etc. as needed
        ],
    ],

    'dependencies' => [
        'factories' => [
            \Doctrine\DBAL\Connection::class => function (ContainerInterface $container): Connection {
                $connectionParams = $container->get('config')['doctrine']['connection'];
                $config = new Configuration();
                $config->setMiddlewares([
                    new DbalLoggingMiddleware($container->get(Psr\Log\LoggerInterface::class))
                ]);
                return DriverManager::getConnection($connectionParams, $config);
            },
        ],
    ],
];
