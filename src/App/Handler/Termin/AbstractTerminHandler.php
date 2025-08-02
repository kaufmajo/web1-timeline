<?php

declare(strict_types=1);

namespace App\Handler\Termin;

use App\Enum\TerminAnsichtEnum;
use App\Handler\AbstractBaseHandler;
use App\Traits\Aware\FormStorageAwareTrait;
use App\Traits\Aware\MediaRepositoryAwareTrait;
use App\Traits\Aware\TerminRepositoryAwareTrait;

abstract class AbstractTerminHandler extends AbstractBaseHandler
{
    use FormStorageAwareTrait;

    use MediaRepositoryAwareTrait;

    use TerminRepositoryAwareTrait;

    public function getMappedDefSearchValues(array $data = []): array
    {
        $searchValues              = [];
        $searchValues['ansicht']   = [TerminAnsichtEnum::TIMELINE->value];
        $searchValues['tagezuvor'] = true;

        return array_merge($searchValues, $data);
    }

    public function getMappedMngSearchValues(array $data = []): array
    {
        $searchValues              = [];
        $searchValues['ansicht']   = [TerminAnsichtEnum::TIMELINE->value, TerminAnsichtEnum::NONE->value];

        return array_merge($searchValues, $data);
    }
}
