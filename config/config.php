<?php

return [
    'enabled' => env('API_EXPLORER_ENABLED', true),
    'cache' => env('API_EXPLORER_CACHE', true),
    'cache_ttl' => env('API_EXPLORER_CACHE_TTL', 86400),
    'route_prefix' => env('API_EXPLORER_ROUTE_PREFIX', 'dev/api-explorer'),
    'middleware' => null,
    'excluded_middleware' => [],
    'exclude_patterns' => [
        'boost',
        'up',
        'storage',
    ],
];
