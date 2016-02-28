<?php

return [
    'database' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'dev123dev',
        'database' => 'archorgau_test',
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
