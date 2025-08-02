<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\AbstractEntity;
use App\Model\Entity\EntityHydratorInterface;
use App\Model\Entity\EntityInterface;
use Doctrine\DBAL\Connection;
use Laminas\Hydrator\HydratorInterface;

use function in_array;

abstract class AbstractRepository implements RepositoryInterface
{
    protected Connection $dbalConnection;

    private EntityHydratorInterface $entityHydrator;

    protected HydratorInterface $hydrator;

    protected EntityInterface $prototype;

    public function __construct(Connection $dbalConnection, EntityHydratorInterface $entityHydrator, AbstractEntity $prototype)
    {
        $this->dbalConnection  = $dbalConnection;
        $this->entityHydrator  = $entityHydrator;
        $this->prototype = $prototype;
    }

    public function setPrototype(AbstractEntity $prototype): void
    {
        $this->prototype = $prototype;
    }

    public function hydrateEntity(array $data): EntityInterface
    {
        return $this->entityHydrator->hydrate($data, clone $this->prototype);
    }

    public function extractEntity(EntityInterface $entity): array
    {
        return $this->entityHydrator->extract($entity);
    }

    public function isParamValid(array $params, string $key, array $falseValue = [null, '']): bool
    {
        if (! isset($params[$key])) {
            return false;
        }

        if (in_array($params[$key], $falseValue, true)) {
            return false;
        }

        return true;
    }
}
