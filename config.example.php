<?php

return [
    'mode' => 'production',
    'site_title' => 'Your Site Name Here',
    'site_email' => 'admin@example.net',
    'database' => [
        'host' => 'localhost',
        'user' => 'database_user_here',
        'password' => 'database_password_here',
        'database' => 'database_name_here',
    ],
    'filesystems' => [
        'cache' => [
            'type' => 'local',
            'root' => __DIR__ . '/data/cache',
        ],
        'storage' => [
            'type' => 'local',
            'root' => __DIR__ . '/data/storage',
        ]
    ],
    'mail' => [
        'transport' => 'mail',
    ],
];
