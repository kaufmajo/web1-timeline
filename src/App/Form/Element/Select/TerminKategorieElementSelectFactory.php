<?php

declare(strict_types=1);

namespace App\Form\Element\Select;

use App\Form;
use Doctrine\DBAL\Connection;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TerminKategorieElementSelectFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TerminKategorieElementSelect
    {
        $dbal = $container->get(Connection::class);

        $element = new Form\Element\Select\TerminKategorieElementSelect();

        $element->setDb($dbal);

        return $element;
    }
}
