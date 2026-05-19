<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\StaffUserModel;

/**
 * Invitation staff : jeton par e-mail, activation et choix du mot de passe.
 */
final class StaffInvite
{
    public static function expiryHours(): int
    {
        $h = (int) env('staff.invite.expiryHours', 24);

        return max(1, min(720, $h));
    }

    /**
     * @return array{token: string, hash: string, expires_at: string}
     */
    public static function generateTokenPayload(): array
    {
        $token   = bin2hex(random_bytes(32));
        $hours   = self::expiryHours();
        $expires = date('Y-m-d H:i:s', time() + $hours * 3600);

        return [
            'token'      => $token,
            'hash'       => self::hashToken($token),
            'expires_at' => $expires,
        ];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function inviteUrl(string $plainToken): string
    {
        helper('url');

        return site_url('admin/invite/' . $plainToken);
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function isPending(array $user): bool
    {
        $hash = trim((string) ($user['invite_token_hash'] ?? ''));
        if ($hash === '') {
            return false;
        }

        $exp = trim((string) ($user['invite_token_expires_at'] ?? ''));
        if ($exp === '') {
            return true;
        }

        return strtotime($exp) !== false && strtotime($exp) >= time();
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function isExpired(array $user): bool
    {
        $hash = trim((string) ($user['invite_token_hash'] ?? ''));
        if ($hash === '') {
            return false;
        }

        $exp = trim((string) ($user['invite_token_expires_at'] ?? ''));
        if ($exp === '') {
            return false;
        }

        return strtotime($exp) !== false && strtotime($exp) < time();
    }

    /**
     * Crée un compte inactif côté mot de passe (hash aléatoire) et envoie l’e-mail d’invitation.
     *
     * @return array{ok: bool, user_id: int, email_sent: bool, error?: string}
     */
    public static function provisionAndNotify(string $email, string $role): array
    {
        $email = mb_strtolower(trim($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'user_id' => 0, 'email_sent' => false, 'error' => 'E-mail invalide.'];
        }

        if (! in_array($role, ['admin', 'editor'], true)) {
            return ['ok' => false, 'user_id' => 0, 'email_sent' => false, 'error' => 'Rôle invalide.'];
        }

        $model = model(StaffUserModel::class);
        if ($model->where('email', $email)->first() !== null) {
            return ['ok' => false, 'user_id' => 0, 'email_sent' => false, 'error' => 'Cet e-mail est déjà utilisé.'];
        }

        $payload = self::generateTokenPayload();
        $tempHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        $model->insert([
            'email'                   => $email,
            'password_hash'           => $tempHash,
            'role'                    => $role,
            'is_active'               => 1,
            'invite_token_hash'       => $payload['hash'],
            'invite_token_expires_at' => $payload['expires_at'],
        ]);

        $userId = (int) $model->getInsertID();
        if ($userId < 1) {
            return ['ok' => false, 'user_id' => 0, 'email_sent' => false, 'error' => 'Création du compte impossible.'];
        }

        $sent = StaffInviteMailer::sendInvitation($email, $payload['token'], $role);

        return [
            'ok'         => true,
            'user_id'    => $userId,
            'email_sent' => $sent,
        ];
    }

    /**
     * Régénère le jeton et renvoie l’e-mail.
     *
     * @return array{ok: bool, email_sent: bool, error?: string}
     */
    public static function resendForUserId(int $userId): array
    {
        $model = model(StaffUserModel::class);
        $user  = $model->find($userId);
        if ($user === null) {
            return ['ok' => false, 'email_sent' => false, 'error' => 'Compte introuvable.'];
        }

        $email = mb_strtolower(trim((string) ($user['email'] ?? '')));
        if ($email === '') {
            return ['ok' => false, 'email_sent' => false, 'error' => 'E-mail manquant.'];
        }

        $payload = self::generateTokenPayload();
        $model->update($userId, [
            'invite_token_hash'       => $payload['hash'],
            'invite_token_expires_at' => $payload['expires_at'],
        ]);

        $role = (string) ($user['role'] ?? 'editor');
        $sent = StaffInviteMailer::sendInvitation($email, $payload['token'], $role);

        return ['ok' => true, 'email_sent' => $sent];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findUserByPlainToken(string $plainToken): ?array
    {
        $plainToken = trim($plainToken);
        if ($plainToken === '' || strlen($plainToken) < 32) {
            return null;
        }

        $user = model(StaffUserModel::class)
            ->where('invite_token_hash', self::hashToken($plainToken))
            ->first();

        if (! is_array($user)) {
            return null;
        }

        if (self::isExpired($user)) {
            return null;
        }

        return $user;
    }

    public static function completeSetup(int $userId, string $plainPassword): bool
    {
        $model = model(StaffUserModel::class);
        $user  = $model->find($userId);
        if ($user === null || ! self::isPending($user)) {
            return false;
        }

        $model->update($userId, [
            'password_hash'           => password_hash($plainPassword, PASSWORD_DEFAULT),
            'invite_token_hash'       => null,
            'invite_token_expires_at' => null,
        ]);

        return true;
    }

    public static function clearInvite(int $userId): void
    {
        model(StaffUserModel::class)->update($userId, [
            'invite_token_hash'       => null,
            'invite_token_expires_at' => null,
        ]);
    }
}
