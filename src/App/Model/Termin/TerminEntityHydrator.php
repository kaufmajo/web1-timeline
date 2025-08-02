<?php

declare(strict_types=1);

namespace App\Model\Termin;

use App\Model\Entity\AbstractEntityHydrator;
use App\Model\Entity\EntityInterface;

class TerminEntityHydrator extends AbstractEntityHydrator
{
    public function hydrate(array $data, EntityInterface $object): TerminEntityInterface
    {
        /** @var TerminEntityInterface $object */

        // hydrate from parent class
        $object = parent::hydrate($data, $object);

        $object->setTerminZeitStart(substr((string)$object->getTerminZeitStart(), 0, -3));
        $object->setTerminZeitEnde(substr((string)$object->getTerminZeitEnde(), 0, -3));

        return $object;
    }

    public function extract(EntityInterface $object): array
    {
        /** @var TerminEntityInterface $object */

        // extract from parent class
        $data = parent::extract($object);

        // time fields
        if ($object->getTerminZeitGanztags()) {
            $data['termin_zeit_start'] = '00:00:00';
            $data['termin_zeit_ende']  = '23:59:59';
        }

        // unset fields
        unset($data['fields']);
        unset($data['termin_serie_intervall']);
        unset($data['termin_serie_wiederholung']);
        unset($data['termin_serie_ende']);

        return $data;
    }
}
