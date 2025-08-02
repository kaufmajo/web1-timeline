<?php

declare(strict_types=1);

namespace App\Traits\Aware;

use Doctrine\DBAL\Connection;

trait DbalAwareTrait
{
    protected Connection $dbalConnection;

    public function getDbalConnection(): Connection
    {
        return $this->dbalConnection;
    }

    public function setDbalConnection(Connection $dbalConnection): void
    {
        $this->dbalConnection = $dbalConnection;
    }
}
