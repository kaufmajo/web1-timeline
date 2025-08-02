<?php

declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Entity\EntityInterface;

abstract class AbstractEntityHydrator implements EntityHydratorInterface
{
    public function hydrate(array $data, EntityInterface $entity): EntityInterface
    {
        return $entity->exchangeArray($data);
    }

    public function extract(EntityInterface $entity): array
    {
        return $entity->getArrayCopy();
    }
}
