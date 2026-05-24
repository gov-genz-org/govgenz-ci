<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProjectContributionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class ProjectContributions extends BaseController
{
    public function index()
    {
        $statusGet = $this->request->getGet('status');
        $filter    = is_string($statusGet) && in_array($statusGet, ['new', 'reviewed', 'rejected'], true)
            ? $statusGet
            : null;

        $model = model(ProjectContributionModel::class);
        if ($filter === 'new') {
            $model = $model->where('status', ProjectContributionModel::STATUS_NEW);
        } elseif ($filter === 'reviewed') {
            $model = $model->where('status', ProjectContributionModel::STATUS_REVIEWED);
        } elseif ($filter === 'rejected') {
            $model = $model->where('status', ProjectContributionModel::STATUS_REJECTED);
        }

        $list = $this->adminPaginatedList(
            $model,
            [
                'created_at'        => 'created_at',
                'donor_name'        => 'donor_name',
                'project_title'     => 'project_title',
                'contribution_type' => 'contribution_type',
                'status'            => 'status',
            ],
            'created_at',
            'desc',
            ['status'],
        );

        return view('admin/layout', [
            'title' => 'Propositions financement',
            'main'  => view('admin/project_contributions/index', [
                'rows'         => $list['rows'],
                'filter'       => $filter ?? 'all',
                'pager'        => $list['pager'],
                'sort'         => $list['sort'],
                'dir'          => $list['dir'],
            ]),
        ]);
    }

    public function setStatus(int $id): ResponseInterface
    {
        $status = $this->request->getPost('status');
        $allowed = [
            ProjectContributionModel::STATUS_NEW,
            ProjectContributionModel::STATUS_REVIEWED,
            ProjectContributionModel::STATUS_REJECTED,
        ];
        if (! is_string($status) || ! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', lang('Admin.error_invalid_status'));
        }

        $model = model(ProjectContributionModel::class);
        $row   = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->update($id, ['status' => $status]);

        return redirect()->back()->with('message', lang('Admin.flash_contribution_updated'));
    }

    public function clearTable(): ResponseInterface
    {
        model(ProjectContributionModel::class)->db->table('project_contributions')->truncate();

        return redirect()->to(site_url('admin/project-contributions'))->with('message', lang('Admin.flash_contributions_cleared'));
    }
}
