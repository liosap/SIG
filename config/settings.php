<?php

declare(strict_types=1);

return [
    'app_name' => 'SIG - Sistema Integral de Gestión',
    'app_env' => $_ENV['APP_ENV'] ?: 'desarrollo',
    'app_debug' => $_ENV['APP_DEBUG'] === 'true',

    'db' => [
        'host' => $_ENV['DB_HOST'] ?: 'localhost',
        'database' => $_ENV['DB_NAME'] ?: 'sig',
        'username' => $_ENV['DB_USER'] ?: 'root',
        'password' => $_ENV['DB_PASS'] ?: '',
    ],
];
