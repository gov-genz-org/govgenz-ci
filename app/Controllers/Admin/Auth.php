<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\StaffAuthPolicy;
use App\Libraries\StaffInvite;
use App\Libraries\StaffLoginAudit;
use App\Models\StaffUserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    /** Tentatives de connexion max par fenêtre (par adresse IP). */
    private const LOGIN_ATTEMPT_CAPACITY = 12;

    /** Fenêtre glissante en secondes (15 minutes). */
    private const LOGIN_ATTEMPT_WINDOW = 900;

    public function loginForm()
    {
        if (session()->get('staff_user_id')) {
            return redirect()->to(site_url('admin'));
        }

        $out = service('request')->getGet('out');

        return view('admin/layout', [
            'title' => 'Connexion',
            'main'  => view('admin/auth/login', [
                'loggedOutBanner'   => $out === '1',
                'loginPasswordHint' => sprintf(
                    'Mot de passe : au moins %d caractères%s. Utilisez l’icône œil pour contrôler votre saisie.',
                    StaffAuthPolicy::loginPasswordMinLength(),
                    ENVIRONMENT === 'production' ? ' (exigence production)' : '',
                ),
            ]),
        ]);
    }

    public function loginAttempt(): ResponseInterface
    {
        $minLen = StaffAuthPolicy::loginPasswordMinLength();
        $rules   = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[' . $minLen . ']',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $emailRaw = $this->request->getPost('email');
        $emailRaw = is_string($emailRaw) ? $emailRaw : '';

        $ipKey     = 'admin_login_ip_' . md5($this->request->getIPAddress());
        $throttler = service('throttler');
        if (! $throttler->check($ipKey, self::LOGIN_ATTEMPT_CAPACITY, self::LOGIN_ATTEMPT_WINDOW)) {
            StaffLoginAudit::record($this->request, 'failure', 'throttle', null, $emailRaw);

            return redirect()->back()->withInput()->with(
                'error',
                $this->throttleWaitMessage($throttler->getTokenTime()),
            );
        }

        $model = model(StaffUserModel::class);
        $user  = $model->where('email', $emailRaw)->first();
        if (
            $user === null
            || ! password_verify((string) $this->request->getPost('password'), $user['password_hash'])
        ) {
            StaffLoginAudit::record($this->request, 'failure', 'invalid_credentials', null, $emailRaw);

            return redirect()->back()->withInput()->with('error', lang('Admin.error_auth_invalid'));
        }

        if ((int) ($user['is_active'] ?? 1) !== 1) {
            StaffLoginAudit::record($this->request, 'failure', 'account_disabled', null, $emailRaw);

            return redirect()->back()->withInput()->with('error', lang('Admin.error_auth_disabled'));
        }

        if (StaffInvite::isPending($user)) {
            StaffLoginAudit::record($this->request, 'failure', 'invite_pending', null, $emailRaw);

            return redirect()->back()->withInput()->with(
                'error',
                'Ce compte n’est pas encore activé. Utilisez le lien reçu par e-mail pour choisir votre mot de passe.',
            );
        }

        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            $model->update($user['id'], [
                'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            ]);
        }

        $throttler->remove($ipKey);

        StaffLoginAudit::record(
            $this->request,
            'success',
            null,
            (int) $user['id'],
            (string) $user['email'],
        );

        // Lecture avant regenerate(true) : l’ancienne session sera détruite.
        $intendedPath = session()->get('admin_intended');
        if (! is_string($intendedPath)) {
            $intendedPath = null;
        }
        session()->remove('admin_intended');

        session()->regenerate(true);
        session()->set([
            'staff_user_id' => $user['id'],
            'staff_email'   => $user['email'],
            'staff_role'    => $user['role'],
        ]);

        return redirect()->to($this->validatedAdminUrlFromPath($intendedPath));
    }

    public function logout(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return redirect()->to(site_url('admin'));
        }

        session()->destroy();

        return redirect()->to(site_url('admin/login?out=1'));
    }

    private function throttleWaitMessage(int $seconds): string
    {
        $sec = max(1, $seconds);
        if ($sec <= 90) {
            return 'Trop de tentatives de connexion. Réessayez dans environ ' . $sec . ' seconde(s).';
        }

        return 'Trop de tentatives de connexion. Réessayez dans environ ' . (int) ceil($sec / 60) . ' minute(s).';
    }

    /**
     * URL interne admin après connexion (évite les redirections ouvertes).
     *
     * @param string|null $path Chemin issu de session admin_intended (lu puis retiré avant regenerate).
     */
    private function validatedAdminUrlFromPath(?string $path): string
    {
        if ($path === null || $path === '') {
            return site_url('admin');
        }

        $path = trim($path, '/');
        if (
            $path === ''
            || $path === 'admin/login'
            || $path === 'admin/logout'
            || ! str_starts_with($path, 'admin')
            || ! preg_match('#^admin(?:/[\w\-]+)*$#', $path)
        ) {
            return site_url('admin');
        }

        return site_url($path);
    }
}
