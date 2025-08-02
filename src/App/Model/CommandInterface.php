<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\EntityInterface;

interface CommandInterface
{
    public function insert(string $table, EntityInterface $entity): int;

    public function update(string $table, EntityInterface $entity, string $entityKey): int;

    public function delete(string $table, EntityInterface $entity, string $entityKey): int;
}
