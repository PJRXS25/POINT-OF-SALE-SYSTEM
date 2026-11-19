<?php

use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Filters;
use Config\Session;

/** @internal */
final class SecurityConfigurationTest extends CIUnitTestCase
{
    public function testSecurityHeadersAndCsrfAreGlobal(): void
    {
        $filters = new Filters();

        $this->assertContains('csrf', $filters->globals['before']);
        $this->assertContains('secureheaders', $filters->globals['after']);
    }

    public function testSessionsUsePersistentDatabaseStorage(): void
    {
        $session = new Session();

        $this->assertSame(DatabaseHandler::class, $session->driver);
        $this->assertSame('ci_sessions', $session->savePath);
    }

    public function testEnvironmentBaseUrlOverridesLocalDefault(): void
    {
        $key = 'app.baseURL';
        $hadEnv = array_key_exists($key, $_ENV);
        $previousEnv = $_ENV[$key] ?? null;

        $_ENV[$key] = 'https://point-of-sale.example/';

        try {
            $app = new App();

            $this->assertSame('https://point-of-sale.example/', $app->baseURL);
        } finally {
            if ($hadEnv) {
                $_ENV[$key] = $previousEnv;
            } else {
                unset($_ENV[$key]);
            }
        }
    }
}
