<?php

return [
    'database' => [
        'host' => 'localhost',
        'user' => 'archorgau',
        'password' => '',
        'database' => 'archorgau',
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
