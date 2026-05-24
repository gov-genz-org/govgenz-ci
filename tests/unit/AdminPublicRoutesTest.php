<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pages admin publiques (sans session) — détecte les régressions de rendu i18n.
 *
 * @internal
 */
final class AdminPublicRoutesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        service('language')->setLocale('fr');
    }

    public function testLoginFormRendersAdminLangStrings(): void
    {
        $result = $this->get('/admin/login');
        $result->assertOK();
        $result->assertSee(lang('Admin.auth_login_title'), 'html');
        $result->assertSee(lang('Admin.auth_login_submit'), 'html');
        $result->assertSee(lang('Admin.auth_view_site'), 'html');
    }

    public function testInviteWithShortTokenShowsInvalidInviteCopy(): void
    {
        // StaffInvite::findUserByPlainToken returns null when token length < 32 (no DB).
        $result = $this->get('/admin/invite/short-invalid-token');
        $result->assertOK();
        $result->assertSee(lang('Admin.auth_invite_invalid_title'), 'html');
        $result->assertSee(lang('Admin.action_back_to_login'), 'html');
    }

    public function testLogoutViaGetRedirectsToAdminArea(): void
    {
        $result = $this->get('/admin/logout');
        $result->assertRedirectTo(site_url('admin'));
    }
}
