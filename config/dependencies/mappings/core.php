<?php

declare(strict_types=1);

return [
    'config' => [
        'app' => [
            'name' => $_SERVER['APP_NAME'] ?? 'Rede Plástica',
            'env' => $_SERVER['APP_ENV'] ?? 'production',
            'base_url' => $_SERVER['APP_BASE_URL'],
        ],
        'slim' => [
            'display_error_details' => ($_SERVER['SLIM_DISPLAY_ERROR_DETAILS'] ?? 'false') === 'true',
            'log_errors' => ($_SERVER['SLIM_LOG_ERRORS'] ?? 'true') === 'true',
            'log_error_details' => ($_SERVER['SLIM_LOG_ERROR_DETAILS'] ?? 'true') === 'true',
        ],
        'log' => [
            'max_app_log_files' => (int)$_SERVER['MAX_APP_LOG_FILES'] ?? 30,
            'max_debug_log_files' => (int)$_SERVER['MAX_DEBUG_LOG_FILES'] ?? 7,
        ],
        'db' => [
            'host' => $_SERVER['DB_HOST'] ?? 'localhost',
            'database' => $_SERVER['DB_NAME'],
            'user' => $_SERVER['DB_USER'],
            'password' => $_SERVER['DB_PASSWORD']
        ],
    ],
];