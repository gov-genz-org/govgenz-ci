<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VolunteerApplicationModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Volunteers extends BaseController
{
    public function index()
    {
        $statusGet = $this->request->getGet('status');
        $filter    = is_string($statusGet) && in_array($statusGet, ['new', 'reviewed'], true)
            ? $statusGet
            : null;

        $model = model(VolunteerApplicationModel::class);
        if ($filter === 'new') {
            $model = $model->where('status', 'new');
        } elseif ($filter === 'reviewed') {
            $model = $model->where('status', 'reviewed');
        }

        $list = $this->adminPaginatedList(
            $model,
            [
                'created_at' => 'created_at',
                'full_name'  => 'full_name',
                'email'      => 'email',
                'status'     => 'status',
            ],
            'created_at',
            'desc',
            ['status'],
        );

        return view('admin/layout', [
            'title' => 'Volontaires',
            'main'  => view('admin/volunteers/index', [
                'rows'             => $list['rows'],
                'volunteerFilter' => $filter ?? 'all',
                'pager'           => $list['pager'],
                'sort'            => $list['sort'],
                'dir'             => $list['dir'],
            ]),
        ]);
    }

    public function setStatus(int $id): ResponseInterface
    {
        $status = $this->request->getPost('status');
        if (! is_string($status) || ! in_array($status, ['new', 'reviewed'], true)) {
            return redirect()->back()->with('error', 'Statut invalide.');
        }

        $model = model(VolunteerApplicationModel::class);
        $row   = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->update($id, ['status' => $status]);

        return redirect()->back()->with('message', 'Candidature mise à jour.');
    }

    public function clearTable(): ResponseInterface
    {
        model(VolunteerApplicationModel::class)->db->table('volunteer_applications')->truncate();

        return redirect()->to(site_url('admin/volunteers'))->with('message', 'Toutes les candidatures volontaires ont été supprimées.');
    }
}
