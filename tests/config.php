<?php

return [
    'mode' => 'development',
    'database' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'dev123dev',
        'database' => 'swidau_test',
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
