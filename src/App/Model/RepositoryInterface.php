<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\EntityInterface;
use Doctrine\DBAL\Connection;

interface RepositoryInterface
{
    public function getDbalConnection(): Connection;

    public function mapReferences(?EntityInterface $entity): ?EntityInterface;
}
