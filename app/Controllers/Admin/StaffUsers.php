<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\StaffAuthPolicy;
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
        $model = model(StaffUserModel::class);
        $users = $model->orderBy('email', 'ASC')->paginate(static::ADMIN_LIST_PER_PAGE, 'default');

        return view('admin/layout', [
            'title' => 'Équipe',
            'main'  => view('admin/staff_users/index', [
                'users' => $users,
                'pager' => $model->pager,
                'roles' => self::ROLE_LABELS,
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
        $minLen = StaffAuthPolicy::loginPasswordMinLength();
        $rules   = [
            'email'    => 'required|valid_email|is_unique[staff_users.email]',
            'password' => 'required|min_length[' . $minLen . ']',
            'role'     => 'required|in_list[admin,editor]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(StaffUserModel::class)->insert([
            'email'         => mb_strtolower(trim((string) $this->request->getPost('email'))),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'          => $this->request->getPost('role'),
            'is_active'     => 1,
        ]);

        return redirect()->to(site_url('admin/staff-users'))->with('message', 'Compte créé.');
    }

    public function edit(int $id): ResponseInterface|string
    {
        $user = model(StaffUserModel::class)->find($id);
        if ($user === null) {
            return redirect()->to(site_url('admin/staff-users'))->with('error', 'Utilisateur introuvable.');
        }

        return view('admin/layout', [
            'title' => 'Modifier le compte',
            'main'  => view('admin/staff_users/form', [
                'user'    => $user,
                'roles'   => self::ROLE_LABELS,
                'is_edit' => true,
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
        if ($pwd !== '') {
            $data['password_hash'] = password_hash($pwd, PASSWORD_DEFAULT);
        }

        $model->update($id, $data);

        return redirect()->to(site_url('admin/staff-users'))->with('message', 'Compte mis à jour.');
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

        return redirect()->back()->with('message', 'Compte supprimé.');
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
