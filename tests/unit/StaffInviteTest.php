<?php

declare(strict_types=1);

use App\Libraries\StaffInvite;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class StaffInviteTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testHashTokenIsDeterministic(): void
    {
        $this->assertSame(
            hash('sha256', 'abc'),
            StaffInvite::hashToken('abc'),
        );
    }

    public function testGenerateTokenPayloadShape(): void
    {
        $payload = StaffInvite::generateTokenPayload();

        $this->assertArrayHasKey('token', $payload);
        $this->assertArrayHasKey('hash', $payload);
        $this->assertArrayHasKey('expires_at', $payload);
        $this->assertSame(64, strlen($payload['token']));
        $this->assertSame(StaffInvite::hashToken($payload['token']), $payload['hash']);
    }

    public function testExpiryHoursClamped(): void
    {
        $hours = StaffInvite::expiryHours();

        $this->assertGreaterThanOrEqual(1, $hours);
        $this->assertLessThanOrEqual(720, $hours);
    }

    public function testIsPendingAndExpired(): void
    {
        $future = date('Y-m-d H:i:s', time() + 3600);
        $past   = date('Y-m-d H:i:s', time() - 3600);

        $pending = [
            'invite_token_hash'       => 'abc',
            'invite_token_expires_at' => $future,
        ];
        $this->assertTrue(StaffInvite::isPending($pending));
        $this->assertFalse(StaffInvite::isExpired($pending));

        $expired = [
            'invite_token_hash'       => 'abc',
            'invite_token_expires_at' => $past,
        ];
        $this->assertFalse(StaffInvite::isPending($expired));
        $this->assertTrue(StaffInvite::isExpired($expired));

        $noInvite = ['invite_token_hash' => ''];
        $this->assertFalse(StaffInvite::isPending($noInvite));
        $this->assertFalse(StaffInvite::isExpired($noInvite));
    }

    public function testCanResendInvite(): void
    {
        $future = date('Y-m-d H:i:s', time() + 3600);
        $past   = date('Y-m-d H:i:s', time() - 3600);

        $pending = [
            'invite_token_hash'       => 'abc',
            'invite_token_expires_at' => $future,
        ];
        $expired = [
            'invite_token_hash'       => 'abc',
            'invite_token_expires_at' => $past,
        ];
        $noInvite = ['invite_token_hash' => ''];

        $this->assertTrue(StaffInvite::canResendInvite($pending));
        $this->assertTrue(StaffInvite::canResendInvite($expired));
        $this->assertFalse(StaffInvite::canResendInvite($noInvite));
    }

    public function testFindUserByPlainTokenRejectsShortTokenWithoutDb(): void
    {
        $this->assertNull(StaffInvite::findUserByPlainToken('short'));
        $this->assertNull(StaffInvite::findUserByPlainToken(''));
    }

    public function testInviteUrlPointsToAdminInvite(): void
    {
        $this->get('/admin/login');
        $url = StaffInvite::inviteUrl('a' . str_repeat('b', 31));

        $this->assertStringContainsString('/admin/invite/', $url);
    }
}
