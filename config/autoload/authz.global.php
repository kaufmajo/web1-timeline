<?php

declare(strict_types=1);

return [
    // ...
    'mezzio-authorization-rbac' => [
        'roles'       => [
            'admin'  => [],
            'termin' => ['admin'],
            'media'  => ['termin'],
        ],
        'permissions' => [
            'media'  => [
                'manage.home.read',
                'manage.media.read',
                'manage.media.version',
                'manage.media.delete',
                'manage.media.insert',
                'manage.media.update',
            ],
            'termin' => [
                'manage.termin.calendar',
                'manage.termin.search',
                'manage.termin.copy',
                'manage.termin.delete',
                'manage.termin.insert',
                'manage.termin.update',
            ],
            'admin'  => [
                'default.app.cleanup',
                'manage.home.read',
                'manage.home.initconfig',
                'manage.home.phpinfo',
                'manage.home.tabellen',
                'manage.home.stats',
            ],
        ],
    ],
];
