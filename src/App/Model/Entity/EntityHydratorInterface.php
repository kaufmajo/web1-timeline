<?php

declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Entity\EntityInterface;

interface EntityHydratorInterface
{
    public function hydrate(array $data, EntityInterface $entity): EntityInterface;

    public function extract(EntityInterface $entity): array;
}
