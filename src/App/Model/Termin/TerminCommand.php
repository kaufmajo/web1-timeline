<?php

declare(strict_types=1);

namespace App\Model\Termin;

use App\Model\AbstractCommand;
use App\Model\Entity\EntityHydratorInterface;
use App\Service\HelperService;
use App\Traits\Aware\TerminRepositoryAwareTrait;
use DateTime;
use Doctrine\DBAL\Connection;

class TerminCommand extends AbstractCommand implements TerminCommandInterface
{
    use TerminRepositoryAwareTrait;

    public function __construct(Connection $dbalConnection, EntityHydratorInterface $entityHydrator)
    {
        parent::__construct($dbalConnection, $entityHydrator);
    }

    public function insertTermin(TerminEntityInterface $terminEntity): int
    {
        return $this->insert('tajo1_termin', $terminEntity);
    }

    public function updateTermin(TerminEntityInterface $terminEntity): int
    {
        return $this->update('tajo1_termin', $terminEntity, 'termin_id');
    }

    public function deleteTermin(TerminEntityInterface $terminEntity): int
    {
        return $this->delete('tajo1_termin', $terminEntity, 'termin_id');
    }

    public function saveTermin(TerminEntityInterface $terminEntity): void
    {
        if (null === $terminEntity->getTerminId()) {
            // insert
            $this->insertTermin($terminEntity);
        } else {
            // update
            $this->updateTermin($terminEntity);
        }

        if ($terminEntity->isSerie()) {
            $this->insertSerie($terminEntity);
        }
    }

    public function insertSerie(TerminEntityInterface $terminEntity): void
    {
        $datePeriod        = HelperService::getSeriePeriod(
            $terminEntity->getTerminDatumStart(),
            $terminEntity->getTerminSerieEnde(),
            $terminEntity->getTerminSerieWiederholung()
        );

        $terminEntityClone = clone $terminEntity;

        $i = 0;

        foreach ($datePeriod as $dt) {

            $dt = DateTime::createFromInterface($dt);

            // skip first iteration
            if (0 === $i++) {
                continue;
            }
            $terminEntityClone->setTerminId(null);
            $terminEntityClone->setTerminDatumStart($dt->format('Y-m-d'));
            $terminEntityClone->setTerminDatumEnde($dt->add($terminEntity->getIntervalDifference())->format('Y-m-d'));
            $this->insertTermin($terminEntityClone);
            $i++;
        }
    }
}
