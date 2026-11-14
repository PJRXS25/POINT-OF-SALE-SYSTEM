<?php

use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Test\CIUnitTestCase;
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
}
