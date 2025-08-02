<?php

declare(strict_types=1);

namespace App\Form\Element\Select;

use Doctrine\DBAL\Connection;
use Laminas\Form\Element;

class TerminKategorieElementSelect extends Element\Select
{
    protected Connection $dbal;

    public function setDb(Connection $dbal): void
    {
        $this->dbal = $dbal;
    }

    public function init(): void
    {
        // if ($this->db) {
        //     $this->setValueOptionsFromDb();
        // }
    }

    public function setValueOptionsFromDb(): void
    {
        $sql = '
                SELECT DISTINCT 
                    termin_kategorie
                FROM
                    tajo1_termin
                ORDER BY
                    termin_kategorie';

        $stmt = $this->dbal->executeQuery($sql);
        $return    = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $return[$row['termin_kategorie']] = [
                'label' => $row['termin_kategorie'],
                'value' => $row['termin_kategorie'],
                //'attributes' => ['data-kalender' => 'calendar-1'],
            ];
        }

        $this->setValueOptions($return);
    }
}
