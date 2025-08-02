<?php

declare(strict_types=1);

namespace App\Model\Termin;

use App\Model\Entity\EntityInterface;
use App\Model\EntityRepositoryInterface;

interface TerminRepositoryInterface extends EntityRepositoryInterface
{
    public function findTerminById(int $id): null|TerminEntityInterface|EntityInterface;

    public function fetchTermin(array $params = [], array $groupBy = [], string $order = ''): array;

    public function fetchMitvon(array $params = [], string $order = ''): array;

    public function fetchKategorie(array $params = [], string $order = ''): array;

    public function fetchBetreff(array $params = [], string $order = ''): array;

    public function fetchLink(array $params = [], string $order = ''): array;

    public function fetchLinkTitel(array $params = [], string $order = ''): array;

    public function fetchImage(array $params = [], string $order = ''): array;
}
