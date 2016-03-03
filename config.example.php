<?php

return [
    'site_title' => 'Your Site Name Here',
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
];
