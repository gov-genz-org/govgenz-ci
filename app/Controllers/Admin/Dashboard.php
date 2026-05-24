<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CmsMediaModel;
use App\Models\CmsPageModel;
use App\Models\CmsPostModel;
use App\Models\ProjectContributionModel;
use App\Models\VolunteerApplicationModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $recentPages = model(CmsPageModel::class)
            ->select('id, slug, title, status, updated_at')
            ->orderBy('updated_at', 'DESC')
            ->limit(6)
            ->findAll();

        $recentPosts = model(CmsPostModel::class)
            ->select('id, slug, title, status, updated_at')
            ->orderBy('updated_at', 'DESC')
            ->limit(6)
            ->findAll();

        return view('admin/layout', [
            'title' => 'Tableau de bord',
            'main'  => view('admin/dashboard', [
                'pagesTotal'      => model(CmsPageModel::class)->countAllResults(),
                'pagesPublished'  => model(CmsPageModel::class)->where('status', 'published')->countAllResults(),
                'pagesDraft'      => model(CmsPageModel::class)->where('status', 'draft')->countAllResults(),
                'postsTotal'      => model(CmsPostModel::class)->countAllResults(),
                'postsPublished'  => model(CmsPostModel::class)->where('status', 'published')->countAllResults(),
                'postsDraft'      => model(CmsPostModel::class)->where('status', 'draft')->countAllResults(),
                'mediaTotal'      => model(CmsMediaModel::class)->countAllResults(),
                'volunteersNew'   => model(VolunteerApplicationModel::class)->where('status', 'new')->countAllResults(),
                'volunteersTotal' => model(VolunteerApplicationModel::class)->countAllResults(),
                'contributionsNew'   => model(ProjectContributionModel::class)->where('status', ProjectContributionModel::STATUS_NEW)->countAllResults(),
                'contributionsTotal' => model(ProjectContributionModel::class)->countAllResults(),
                'recentPages'     => $recentPages,
                'recentPosts'     => $recentPosts,
            ]),
        ]);
    }
}
