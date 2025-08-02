<?php

declare(strict_types=1);

namespace App\Model\Media;

use interop\container\containerinterface;

class MediaInputFilterFactory
{
    public function __invoke(containerinterface $container): \App\Model\Media\MediaInputFilter
    {
        $inputFilter = new MediaInputFilter();

        $inputFilter->init();

        return $inputFilter;
    }
}
