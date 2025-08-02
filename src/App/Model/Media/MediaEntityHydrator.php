<?php

declare(strict_types=1);

namespace App\Model\Media;

use App\Model\Entity\AbstractEntityHydrator;
use App\Model\Entity\EntityInterface;

class MediaEntityHydrator extends AbstractEntityHydrator
{
    public function hydrate(array $data, EntityInterface $object): MediaEntityInterface
    {
        /** @var MediaEntityInterface $object */

        // hydrate from parent class
        $object = parent::hydrate($data, $object);

        return $object;
    }

    public function extract(EntityInterface $object): array
    {
        /** @var MediaEntityInterface $object */

        // extract from parent class
        $data = parent::extract($object);

         // unset
        if (null === $data['media_groesse']) {
            unset($data['media_groesse']);
        }

        if (null === $data['media_mimetype']) {
            unset($data['media_mimetype']);
        }

        if (null === $data['media_name']) {
            unset($data['media_name']);
        }

        if (null === $data['media_hash']) {
            unset($data['media_hash']);
        }

        return $data;
    }
}
