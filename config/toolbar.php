<?php

declare(strict_types=1);

return [
    'enabled' => env('LARAVEL_TOOLBAR_ENABLED', true),
    'visible' => env('LARAVEL_TOOLBAR_VISIBLE', true),
    'request_data_ttl' => env('LARAVEL_TOOLBAR_REQUEST_DATA_TTL', 30),
    'request_data_allowed_environments' => ['local', 'development'],
];
