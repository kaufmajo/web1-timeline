<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\EntityHydratorInterface;
use App\Model\Entity\EntityInterface;
use Doctrine\DBAL\Connection;
use RuntimeException;

abstract class AbstractCommand implements CommandInterface
{
    public function __construct(
        protected Connection $dbalConnection,
        protected EntityHydratorInterface $entityHydrator
    ) {}

    public function extractEntity(EntityInterface $entity): array
    {
        return $this->entityHydrator->extract($entity);
    }

    public function insert(string $table, EntityInterface $entity): int
    {
        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->insert($table);

        foreach ($this->extractEntity($entity) as $key => $value) {
            $qb->setValue($key, ':' . $key);
            $qb->setParameter($key, $value);
        }

        $entity->setLastEntityAction('insert');

        $affectedRows = $qb->executeStatement();

        $generatedValue = (int) $this->dbalConnection->lastInsertId();

        $entity->setEntityId($generatedValue);

        return $affectedRows;
    }

    public function update(string $table, EntityInterface $entity, string $entityKey): int
    {
        if (!$entity->getEntityId()) {
            throw new RuntimeException('Cannot update entity; missing identifier');
        }

        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->update($table);

        foreach ($this->extractEntity($entity) as $key => $value) {
            $qb->set($key, ':' . $key);
            $qb->setParameter($key, $value);
        }

        $qb->where("$entityKey = :entity_id")
            ->setParameter('entity_id', $entity->getEntityId());

        $affectedRows = $qb->executeStatement();

        $entity->setLastEntityAction('update');

        return $affectedRows;
    }

    public function delete(string $table, EntityInterface $entity, string $entityKey): int
    {
        if (!$entity->getEntityId()) {
            throw new RuntimeException('Cannot delete entity; missing identifier');
        }

        $qb = $this->dbalConnection->createQueryBuilder();
        $qb->delete($table);

        $qb->where("$entityKey = :entity_id")
            ->setParameter('entity_id', $entity->getEntityId());

        $affectedRows = $qb->executeStatement();

        $entity->setLastEntityAction('delete');

        return $affectedRows;
    }
}
