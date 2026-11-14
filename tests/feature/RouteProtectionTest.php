<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class RouteProtectionTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testStaffDashboardRedirectsGuestsToLogin(): void
    {
        $result = $this->get('/dashboard');

        $result->assertRedirectTo('/login');
    }

    public function testCustomerProfileRedirectsGuestsToCustomerLogin(): void
    {
        $result = $this->get('/customer/profile');

        $result->assertRedirectTo('/customer/login');
    }
}
