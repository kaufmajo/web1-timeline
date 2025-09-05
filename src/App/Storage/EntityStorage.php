<?php

declare(strict_types=1);

namespace App\Storage;

use App\Model\Entity\EntityInterface;
use Exception;

class EntityStorage
{
    protected array $entityArray = [];

    public function set(string $key, EntityInterface $entity, ?EntityInterface $protoType = null): void
    {
        if ($entity instanceof EntityInterface) {
            $this->entityArray[$key] = $entity;
        } elseif ($protoType) {
            $this->entityArray[$key] = $protoType;
        }
    }

    /**
     * @throws Exception
     */
    public function get(string $key): EntityInterface
    {
        if (isset($this->entityArray[$key])) {
            return $this->entityArray[$key];
        }

        throw new Exception('Entity is not in storage: ' . $key);
    }
}
