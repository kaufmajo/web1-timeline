<?php

declare(strict_types=1);

namespace App\Model\Termin;

use Doctrine\DBAL\Connection;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TerminCommandFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TerminCommand
    {
        $dbalConnection = $container->get(Connection::class);

        // repository
        $terminRepository = $container->get(TerminRepositoryInterface::class);

        // return instance
        $returnInstance = new TerminCommand($dbalConnection, new TerminEntityHydrator());
        $returnInstance->setTerminRepository($terminRepository);

        return $returnInstance;
    }
}
