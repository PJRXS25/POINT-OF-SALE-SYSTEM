<?php

declare(strict_types=1);

$database = getenv('E2E_DB_DATABASE') ?: 'pos_system_e2e';
if (!str_ends_with($database, '_e2e')) {
    http_response_code(500);
    echo 'E2E database names must end with _e2e.';
    exit(1);
}

$defaults = [
    'E2E_APP_BASE_URL'    => 'http://127.0.0.1:8099/',
    'E2E_DB_HOST'         => '127.0.0.1',
    'E2E_DB_DATABASE'     => $database,
    'E2E_DB_USERNAME'     => 'root',
    'E2E_DB_PASSWORD'     => '',
    'E2E_DB_DRIVER'       => 'MySQLi',
    'E2E_DB_PORT'         => '3306',
    'E2E_MEDIA_STORAGE'   => 'database',
];

foreach ($defaults as $key => $default) {
    if (getenv($key) === false) {
        putenv($key . '=' . $default);
        $_ENV[$key] = $default;
        $_SERVER[$key] = $default;
    }
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$publicFile = realpath(__DIR__ . '/../../public' . ($path === '/' ? '' : $path));
$publicRoot = realpath(__DIR__ . '/../../public');

if ($path !== '/' && $publicFile !== false && $publicRoot !== false && str_starts_with($publicFile, $publicRoot) && is_file($publicFile)) {
    return false;
}

require __DIR__ . '/../../public/index.php';
