<?php

declare(strict_types=1);

use App\Libraries\StaffInvite;
use App\Models\StaffUserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 */
final class AdminStaffResendInviteFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';

    protected $migrate = true;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();
        service('language')->setLocale('fr');
    }

    public function testResendInviteRequiresAuthentication(): void
    {
        $userId = $this->insertStaffUser([
            'email'                   => 'pending@example.com',
            'invite_token_hash'       => hash('sha256', 'pending-token'),
            'invite_token_expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);

        $result = $this->postWithCsrfFromLogin('admin/staff-users/resend-invite/' . $userId);

        $result->assertRedirect();
        $this->assertStringContainsString(
            'admin/login',
            (string) $result->response()->getHeaderLine('Location'),
        );
    }

    public function testResendInviteRequiresAdminRole(): void
    {
        $userId = $this->insertStaffUser([
            'email'                   => 'expired@example.com',
            'invite_token_hash'       => hash('sha256', 'expired-token'),
            'invite_token_expires_at' => date('Y-m-d H:i:s', time() - 3600),
        ]);

        $result = $this->postWithAdminCsrf(
            'admin/staff-users/resend-invite/' . $userId,
            $this->editorSession(),
        );

        $result->assertRedirect();
        $this->assertStringContainsString(
            site_url('admin'),
            (string) $result->response()->getHeaderLine('Location'),
        );
        $this->assertSame(
            'Accès réservé aux administrateurs.',
            session()->getFlashdata('error'),
        );
    }

    public function testResendInviteRejectedForActivatedAccount(): void
    {
        $userId = $this->insertStaffUser([
            'email'                   => 'active@example.com',
            'invite_token_hash'       => null,
            'invite_token_expires_at' => null,
        ]);

        $result = $this->postWithAdminCsrf(
            'admin/staff-users/resend-invite/' . $userId,
            $this->adminSession(),
        );

        $result->assertRedirect();
        $this->assertSame(
            lang('Admin.error_staff_resend_not_allowed'),
            session()->getFlashdata('error'),
        );
    }

    public function testResendInviteRenewsExpiredToken(): void
    {
        $oldHash = hash('sha256', 'expired-token');
        $userId  = $this->insertStaffUser([
            'email'                   => 'expired@example.com',
            'invite_token_hash'       => $oldHash,
            'invite_token_expires_at' => date('Y-m-d H:i:s', time() - 7200),
        ]);

        $result = $this->postWithAdminCsrf(
            'admin/staff-users/resend-invite/' . $userId,
            $this->adminSession(),
        );

        $result->assertRedirect();

        $flash = session()->getFlashdata('message');
        $this->assertContains($flash, [
            lang('Admin.flash_staff_invite_resent'),
            lang('Admin.flash_staff_token_no_email'),
        ], 'Expected success flash (email sent or SMTP unavailable in CI).');

        $updated = model(StaffUserModel::class)->find($userId);
        $this->assertIsArray($updated);
        $this->assertNotSame($oldHash, $updated['invite_token_hash'] ?? '');
        $this->assertTrue(StaffInvite::isPending($updated));
        $this->assertFalse(StaffInvite::isExpired($updated));
    }

    public function testResendInviteWorksForPendingToken(): void
    {
        $oldHash = hash('sha256', 'still-valid');
        $userId  = $this->insertStaffUser([
            'email'                   => 'pending@example.com',
            'invite_token_hash'       => $oldHash,
            'invite_token_expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);

        $result = $this->postWithAdminCsrf(
            'admin/staff-users/resend-invite/' . $userId,
            $this->adminSession(),
        );

        $result->assertRedirect();
        $this->assertNotNull(session()->getFlashdata('message'));

        $updated = model(StaffUserModel::class)->find($userId);
        $this->assertIsArray($updated);
        $this->assertNotSame($oldHash, $updated['invite_token_hash'] ?? '');
        $this->assertTrue(StaffInvite::canResendInvite($updated));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function insertStaffUser(array $overrides): int
    {
        $now = date('Y-m-d H:i:s');
        $row = array_merge([
            'email'                   => 'user@example.com',
            'password_hash'           => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
            'invite_token_hash'       => null,
            'invite_token_expires_at' => null,
            'role'                    => 'editor',
            'is_active'               => 1,
            'notify_form_submissions' => 1,
            'created_at'              => $now,
            'updated_at'              => $now,
        ], $overrides);

        model(StaffUserModel::class)->insert($row);
        $id = (int) model(StaffUserModel::class)->getInsertID();
        $this->assertGreaterThan(0, $id);

        return $id;
    }

    /**
     * @return array<string, int|string>
     */
    private function adminSession(): array
    {
        return [
            'staff_user_id' => 1,
            'staff_email'   => 'admin@test.example',
            'staff_role'    => 'admin',
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function editorSession(): array
    {
        return [
            'staff_user_id' => 2,
            'staff_email'   => 'editor@test.example',
            'staff_role'    => 'editor',
        ];
    }

    /**
     * @param array<string, int|string> $session
     */
    private function postWithAdminCsrf(string $uri, array $session): TestResponse
    {
        // Guide CMS : pas de requête BDD, accessible éditeur + admin (contrairement à staff-users / dashboard).
        $page = $this->withSession($session)->get('admin/cms-guide');
        $page->assertOK();

        return $this->postWithCsrfFromResponse($page, $uri, $session);
    }

    private function postWithCsrfFromLogin(string $uri): TestResponse
    {
        $page = $this->get('admin/login');
        $page->assertOK();

        return $this->postWithCsrfFromResponse($page, $uri, []);
    }

    /**
     * @param array<string, int|string> $session
     */
    private function postWithCsrfFromResponse(TestResponse $page, string $uri, array $session): TestResponse
    {
        $cookie = $page->response()->getCookie('csrf_cookie_name');
        $this->assertNotNull($cookie);
        $_COOKIE['csrf_cookie_name'] = $cookie->getValue();

        if (
            preg_match(
                '/name="csrf_test_name" value="([^"]+)"/',
                $page->getBody(),
                $matches,
            ) !== 1
        ) {
            $this->fail('Missing csrf_test_name in HTML.');
        }

        $payload = ['csrf_test_name' => $matches[1]];

        return $session === []
            ? $this->post($uri, $payload)
            : $this->withSession($session)->post($uri, $payload);
    }
}
