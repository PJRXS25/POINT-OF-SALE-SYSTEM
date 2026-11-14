<?php

declare(strict_types=1);

/**
 * Maps isolated end-to-end test variables to CodeIgniter's dotted config keys.
 *
 * CodeIgniter loads the project .env file during bootstrap, so this file is
 * intended to be used through PHP's auto_prepend_file setting. The mapped
 * values are written to both environment arrays before the framework starts.
 */
$e2eEnvironment = [
    'app.baseURL'              => 'E2E_APP_BASE_URL',
    'app_baseURL'              => 'E2E_APP_BASE_URL',
    'database.default.hostname' => 'E2E_DB_HOST',
    'database.default.database' => 'E2E_DB_DATABASE',
    'database.default.username' => 'E2E_DB_USERNAME',
    'database.default.password' => 'E2E_DB_PASSWORD',
    'database.default.DBDriver' => 'E2E_DB_DRIVER',
    'database.default.port'     => 'E2E_DB_PORT',
    'seed.adminPassword'        => 'E2E_ADMIN_PASSWORD',
    'media.storage'             => 'E2E_MEDIA_STORAGE',
];

foreach ($e2eEnvironment as $configKey => $environmentKey) {
    $value = getenv($environmentKey);

    if ($value === false) {
        continue;
    }

    $_ENV[$configKey]    = $value;
    $_SERVER[$configKey] = $value;
    putenv($configKey . '=' . $value);
}

unset($e2eEnvironment, $configKey, $environmentKey, $value);
