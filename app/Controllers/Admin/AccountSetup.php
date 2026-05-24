<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\StaffAuthPolicy;
use App\Libraries\StaffInvite;
use App\Libraries\StaffLoginAudit;
use CodeIgniter\HTTP\ResponseInterface;

class AccountSetup extends BaseController
{
    public function form(string $token)
    {
        if (session()->get('staff_user_id')) {
            return redirect()->to(site_url('admin'));
        }

        $user = StaffInvite::findUserByPlainToken($token);
        if ($user === null) {
            return view('admin/layout', [
                'title' => 'Invitation invalide',
                'main'  => view('admin/auth/invite_invalid'),
            ]);
        }

        $minLen = StaffAuthPolicy::loginPasswordMinLength();

        return view('admin/layout', [
            'title' => 'Activer votre compte',
            'main'  => view('admin/auth/invite_setup', [
                'token'            => $token,
                'email'            => (string) ($user['email'] ?? ''),
                'passwordMin'      => $minLen,
                'passwordHint'     => sprintf(
                    'Choisissez un mot de passe d’au moins %d caractères.',
                    $minLen,
                ),
            ]),
        ]);
    }

    public function submit(string $token): ResponseInterface
    {
        if (session()->get('staff_user_id')) {
            return redirect()->to(site_url('admin'));
        }

        $user = StaffInvite::findUserByPlainToken($token);
        if ($user === null) {
            return redirect()->to(site_url('admin/login'))
                ->with('error', lang('Admin.error_invite_invalid'));
        }

        $minLen = StaffAuthPolicy::loginPasswordMinLength();
        $rules  = [
            'password'         => 'required|min_length[' . $minLen . ']',
            'password_confirm' => 'required|matches[password]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $password = (string) $this->request->getPost('password');
        $userId   = (int) ($user['id'] ?? 0);
        if (! StaffInvite::completeSetup($userId, $password)) {
            return redirect()->back()->with('error', lang('Admin.error_password_save_failed'));
        }

        StaffLoginAudit::record(
            $this->request,
            'success',
            'invite_setup',
            $userId,
            (string) ($user['email'] ?? ''),
        );

        session()->regenerate(true);
        session()->set([
            'staff_user_id' => $userId,
            'staff_email'   => $user['email'],
            'staff_role'    => $user['role'],
        ]);

        return redirect()->to(site_url('admin'))
            ->with('message', lang('Admin.flash_account_welcome'));
    }
}
