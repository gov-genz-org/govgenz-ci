<?php

declare(strict_types=1);

use App\Controllers\Admin\Auth;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuthValidatedAdminUrlTest extends CIUnitTestCase
{
    public function testValidatedAdminUrlDefaultsToDashboard(): void
    {
        $invoke = self::getPrivateMethodInvoker(new Auth(), 'validatedAdminUrlFromPath');

        $this->assertSame(site_url('admin'), $invoke(null));
        $this->assertSame(site_url('admin'), $invoke(''));
    }

    public function testValidatedAdminUrlAllowsKnownAdminPaths(): void
    {
        $invoke = self::getPrivateMethodInvoker(new Auth(), 'validatedAdminUrlFromPath');

        $this->assertSame(site_url('admin/pages'), $invoke('admin/pages'));
        $this->assertSame(site_url('admin/posts/edit/3'), $invoke('admin/posts/edit/3'));
        $this->assertSame(site_url('admin/login-events'), $invoke('admin/login-events'));
        $this->assertSame(site_url('admin/staff-users/edit/2'), $invoke('admin/staff-users/edit/2'));
    }

    public function testValidatedAdminUrlRejectsOpenRedirects(): void
    {
        $invoke = self::getPrivateMethodInvoker(new Auth(), 'validatedAdminUrlFromPath');
        $fallback = site_url('admin');

        $this->assertSame($fallback, $invoke('admin/login'));
        $this->assertSame($fallback, $invoke('admin/logout'));
        $this->assertSame($fallback, $invoke('https://evil.example/phishing'));
        $this->assertSame($fallback, $invoke('notadmin/pages'));
        $this->assertSame($fallback, $invoke('admin/../../../etc/passwd'));
        $this->assertSame($fallback, $invoke('admin/pages<script>'));
    }
}
