<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/test',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php84: true)
    ->withPreparedSets(typeDeclarations: true)
    //->withTypeCoverageLevel(50)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withSkip([
        FirstClassCallableRector::class => [
            __DIR__ . '/src/App/Plates/Extension/ColorExtension.php',
            __DIR__ . '/src/App/Plates/Extension/MediaExtension.php',
            __DIR__ . '/src/App/Plates/Extension/QuoteExtension.php',
            __DIR__ . '/src/App/Plates/Extension/UrlpoolExtension.php',
        ],
    ]);
