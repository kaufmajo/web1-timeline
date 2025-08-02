<?php

declare(strict_types=1);

namespace App\Model\Media;

use Doctrine\DBAL\Connection;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MediaCommandFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MediaCommand
    {
        $dbalConnection = $container->get(Connection::class);

        // repository
        $mediaRepository = $container->get(MediaRepositoryInterface::class);

        // return instance
        $returnInstance = new MediaCommand($dbalConnection, new MediaEntityHydrator());
        $returnInstance->setMediaRepository($mediaRepository);

        return $returnInstance;
    }
}
