<?php

declare(strict_types=1);

return [
    'plates'    => [
        'extensions' => [
            // string service names or class names of Plates extensions
            App\Plates\Extension\ColorExtension::class,
            App\Plates\Extension\MediaExtension::class,
            App\Plates\Extension\QuoteExtension::class,
            App\Plates\Extension\UrlpoolExtension::class,
        ],
    ],
    'templates' => [
        'extension' => 'phtml', // change this if you use a different file
        // extension for templates
        'paths' => [
            // namespace => [paths] pairs
        ],
    ],
];
