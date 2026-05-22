<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\StaffAuthPolicy;
use App\Libraries\StaffInvite;
use App\Models\StaffUserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class StaffUsers extends BaseController
{
    /** @var array<string, string> */
    private const ROLE_LABELS = [
        'admin'  => 'Administrateur',
        'editor' => 'Éditeur',
    ];

    public function index()
    {
        $list = $this->adminPaginatedList(
            model(StaffUserModel::class),
            [
                'email'     => 'email',
                'role'      => 'role',
                'is_active' => 'is_active',
            ],
            'email',
            'asc',
        );

        $staffModel = model(StaffUserModel::class);

        return view('admin/layout', [
            'title' => 'Équipe',
            'main'  => view('admin/staff_users/index', [
                'users'             => $list['rows'],
                'pager'             => $list['pager'],
                'sort'              => $list['sort'],
                'dir'               => $list['dir'],
                'roles'             => self::ROLE_LABELS,
                'hasNotifyColumn'   => $staffModel->db->fieldExists('notify_form_submissions', 'staff_users'),
            ]),
        ]);
    }

    public function create()
    {
        return view('admin/layout', [
            'title' => 'Inviter un compte',
            'main'  => view('admin/staff_users/form', [
                'user'    => null,
                'roles'   => self::ROLE_LABELS,
                'is_edit' => false,
            ]),
        ]);
    }

    public function store(): ResponseInterface
    {
        $rules = [
            'email' => 'required|valid_email|is_unique[staff_users.email]',
            'role'  => 'required|in_list[admin,editor]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = StaffInvite::provisionAndNotify(
            (string) $this->request->getPost('email'),
            (string) $this->request->getPost('role'),
        );

        if (! $result['ok']) {
            return redirect()->back()->withInput()->with('error', $result['error'] ?? 'Impossible de créer le compte.');
        }

        $msg = 'Invitation envoyée par e-mail.';
        if (! $result['email_sent']) {
            $msg = 'Compte créé, mais l’e-mail d’invitation n’a pas pu être envoyé (vérifiez la configuration SMTP).';
        }

        return $this->adminRedirectToEdit('admin/staff-users', $result['user_id'], $msg);
    }

    public function resendInvite(int $id): ResponseInterface
    {
        $result = StaffInvite::resendForUserId($id);
        if (! $result['ok']) {
            return redirect()->back()->with('error', $result['error'] ?? 'Envoi impossible.');
        }

        $msg = $result['email_sent']
            ? 'Invitation renvoyée par e-mail.'
            : 'Jeton régénéré, mais l’e-mail n’a pas pu être envoyé (vérifiez la configuration SMTP).';

        return redirect()->back()->with('message', $msg);
    }

    public function edit(int $id): ResponseInterface|string
    {
        $user = model(StaffUserModel::class)->find($id);
        if ($user === null) {
            return redirect()->to(site_url('admin/staff-users'))->with('error', 'Utilisateur introuvable.');
        }

        $staffModel = model(StaffUserModel::class);

        return view('admin/layout', [
            'title' => 'Modifier le compte',
            'main'  => view('admin/staff_users/form', [
                'user'            => $user,
                'roles'           => self::ROLE_LABELS,
                'is_edit'         => true,
                'hasNotifyColumn' => $staffModel->db->fieldExists('notify_form_submissions', 'staff_users'),
            ]),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(StaffUserModel::class);
        $user  = $model->find($id);
        if ($user === null) {
            return redirect()->to(site_url('admin/staff-users'))->with('error', 'Utilisateur introuvable.');
        }

        $rules = [
            'role'      => 'required|in_list[admin,editor]',
            'is_active' => 'required|in_list[0,1]',
        ];
        if ($model->db->fieldExists('notify_form_submissions', 'staff_users')) {
            $rules['notify_form_submissions'] = 'required|in_list[0,1]';
        }
        $pwd = trim((string) $this->request->getPost('password'));
        $minLen = StaffAuthPolicy::loginPasswordMinLength();
        if ($pwd !== '') {
            $rules['password'] = 'min_length[' . $minLen . ']';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleNew      = (string) $this->request->getPost('role');
        $isActiveNew  = (int) $this->request->getPost('is_active');
        $roleOld      = (string) ($user['role'] ?? '');
        $isActiveOld  = (int) ($user['is_active'] ?? 1);

        if (! $this->canDemoteAdmin($model, $user, $roleOld, $roleNew)) {
            return redirect()->back()->withInput()->with(
                'error',
                'Impossible de retirer le dernier administrateur actif.',
            );
        }

        if (! $this->canDeactivateAdmin($model, $user, $isActiveOld, $isActiveNew)) {
            return redirect()->back()->withInput()->with(
                'error',
                'Impossible de désactiver le dernier administrateur actif.',
            );
        }

        $data = [
            'role'      => $roleNew,
            'is_active' => $isActiveNew,
        ];
        if ($model->db->fieldExists('notify_form_submissions', 'staff_users')) {
            $data['notify_form_submissions'] = (int) $this->request->getPost('notify_form_submissions');
        }
        if ($pwd !== '') {
            $data['password_hash']           = password_hash($pwd, PASSWORD_DEFAULT);
            $data['invite_token_hash']       = null;
            $data['invite_token_expires_at'] = null;
        }

        $model->update($id, $data);

        return $this->adminRedirectToEdit('admin/staff-users', $id, 'Compte mis à jour.');
    }

    public function delete(int $id): ResponseInterface
    {
        $currentId = (int) session()->get('staff_user_id');
        if ($id === $currentId) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $model = model(StaffUserModel::class);
        $user  = $model->find($id);
        if ($user === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $role     = (string) ($user['role'] ?? '');
        $isActive = (int) ($user['is_active'] ?? 1) === 1;
        if ($role === 'admin' && $isActive && $this->countOtherActiveAdmins($model, $id) < 1) {
            return redirect()->back()->with('error', 'Impossible de supprimer le dernier administrateur actif.');
        }

        $model->delete($id, true);

        return redirect()->back()->with('message', lang('Admin.flash_staff_deleted'));
    }

    public function toggleFormNotify(int $id): ResponseInterface
    {
        $model = model(StaffUserModel::class);
        if (! $model->db->fieldExists('notify_form_submissions', 'staff_users')) {
            return redirect()->back()->with(
                'error',
                'Option indisponible : exécutez les migrations (notify_form_submissions).',
            );
        }

        $user = $model->find($id);
        if ($user === null) {
            return redirect()->to(site_url('admin/staff-users'))->with('error', 'Utilisateur introuvable.');
        }

        $current = (int) ($user['notify_form_submissions'] ?? 1);
        $next    = $current === 1 ? 0 : 1;
        $model->update($id, ['notify_form_submissions' => $next]);

        $email = (string) ($user['email'] ?? '');
        $msg   = $next === 1
            ? 'Notifications formulaires activées pour ' . $email . '.'
            : 'Notifications formulaires désactivées pour ' . $email . '.';

        return redirect()->back()->with('message', $msg);
    }

    public function clearTable(): ResponseInterface
    {
        $currentId = (int) session()->get('staff_user_id');
        if ($currentId < 1) {
            return redirect()->to(site_url('admin/staff-users'))->with('error', 'Session invalide.');
        }

        model(StaffUserModel::class)->where('id !=', $currentId)->delete();

        return redirect()->to(site_url('admin/staff-users'))->with(
            'message',
            'Les autres comptes ont été supprimés. Le vôtre est conservé.',
        );
    }

    /**
     * @param array<string, mixed> $user
     */
    private function canDemoteAdmin(StaffUserModel $model, array $user, string $roleOld, string $roleNew): bool
    {
        if ($roleOld !== 'admin' || $roleNew === 'admin') {
            return true;
        }

        return $this->countOtherActiveAdmins($model, (int) $user['id']) >= 1;
    }

    /**
     * @param array<string, mixed> $user
     */
    private function canDeactivateAdmin(StaffUserModel $model, array $user, int $wasActive, int $willActive): bool
    {
        if ($wasActive !== 1 || $willActive === 1) {
            return true;
        }
        if (($user['role'] ?? '') !== 'admin') {
            return true;
        }

        return $this->countOtherActiveAdmins($model, (int) $user['id']) >= 1;
    }

    private function countOtherActiveAdmins(StaffUserModel $model, int $excludeId): int
    {
        return (int) $model->where('role', 'admin')
            ->where('is_active', 1)
            ->where('id !=', $excludeId)
            ->countAllResults();
    }
}
