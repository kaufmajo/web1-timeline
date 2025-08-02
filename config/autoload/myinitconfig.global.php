<?php

declare(strict_types=1);

return [
    'my_init_config' => [
        'cleanup_tage_termine'           => 370, // in days
        'cleanup_tage_history'           => 30,  // in days
        'cleanup_tage_termin_history'    => 370, // in days
        'considered_as_new'             => 3,    // in days
        'considered_as_updated'         => 3,    // in days
        'cache'                          => [
            'browser' => [
                'image_lifetime' => 60 * 60 * 24 * 3, // in seconds
            ],
        ],
    ],
];
