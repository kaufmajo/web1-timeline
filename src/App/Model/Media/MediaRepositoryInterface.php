<?php

declare(strict_types=1);

namespace App\Model\Media;

use App\Model\Entity\EntityInterface;
use App\Model\EntityRepositoryInterface;

interface MediaRepositoryInterface extends EntityRepositoryInterface
{
    public function refreshEntity(MediaEntityInterface &$mediaEntity);

    public function findMediaById(int $id): null|MediaEntityInterface|EntityInterface;

    public function fetchMedia(array $params = [], string $order = ''): array;

    public function fetchTag(array $params = [], string $order = ''): array;
}
