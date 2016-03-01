<?php

return [
    'site_title' => 'Your Site Name Here',
    'database' => [
        'host' => 'localhost',
        'user' => 'swidau',
        'password' => '',
        'database' => 'swidau',
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
