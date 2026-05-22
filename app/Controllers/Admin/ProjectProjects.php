<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\LocaleSlug;
use App\Libraries\ProjectAdminForm;
use App\Libraries\ProjectBodyBlocksNormalizer;
use App\Libraries\ProjectBudgetTableSync;
use App\Libraries\ProjectGeographyPayload;
use App\Models\ProjectProjectModel;
use App\Models\SectorModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class ProjectProjects extends BaseController
{
    public function index()
    {
        $model = model(ProjectProjectModel::class);

        $loc = $this->request->getGet('loc');
        if (is_string($loc) && in_array($loc, ['fr', 'en'], true)) {
            $model = $model->where('locale', $loc);
        }

        $status = $this->request->getGet('status');
        if (is_string($status) && in_array($status, array_keys(ProjectProjectModel::projectStatusLabels()), true)) {
            $model = $model->where('project_status', $status);
        }

        $pub = $this->request->getGet('pub');
        if (is_string($pub) && in_array($pub, array_keys(ProjectProjectModel::publicationStateLabels()), true)) {
            $model = $model->where('publication_state', $pub);
        }

        $searchQuery = trim((string) $this->request->getGet('q'));
        if ($searchQuery !== '') {
            if (mb_strlen($searchQuery) > 120) {
                $searchQuery = mb_substr($searchQuery, 0, 120);
            }
            $model = $model->groupStart()->like('title', $searchQuery)->orLike('slug', $searchQuery)->groupEnd();
        }

        $list = $this->adminPaginatedList(
            $model,
            [
                'slug'               => 'slug',
                'locale'             => 'locale',
                'title'              => 'title',
                'project_status'     => 'project_status',
                'publication_state'  => 'publication_state',
                'updated_at'         => 'updated_at',
            ],
            'updated_at',
            'desc',
            ['status', 'pub', 'q', 'loc'],
        );

        return view('admin/layout', [
            'title' => 'Projets (programme)',
            'main'  => view('admin/project_projects/index', [
                'rows'                        => $list['rows'],
                'pager'                       => $list['pager'],
                'sort'                        => $list['sort'],
                'dir'                         => $list['dir'],
                'filterStatus'                => is_string($status) && in_array($status, array_keys(ProjectProjectModel::projectStatusLabels()), true) ? $status : 'all',
                'filterPub'                   => is_string($pub) && in_array($pub, array_keys(ProjectProjectModel::publicationStateLabels()), true) ? $pub : 'all',
                'filterLocale'                => is_string($loc) && in_array($loc, ['fr', 'en'], true) ? $loc : 'all',
                'searchQuery'                 => $searchQuery,
                'statusLabels'                => ProjectProjectModel::projectStatusLabels(),
                'pubLabels'                   => ProjectProjectModel::publicationStateLabels(),
                'translationLocalesByGroup' => $this->translationLocalesByGroupForRows($list['rows'], ProjectProjectModel::class),
            ]),
        ]);
    }

    public function create()
    {
        $formData = ProjectAdminForm::formViewData(null);

        return view('admin/layout', [
            'title'         => 'Nouveau projet',
            'main'          => view('admin/project_projects/form', $formData),
            'extraScripts'  => ProjectAdminForm::editorScripts($formData),
        ]);
    }

    public function store(): ResponseInterface
    {
        if (! $this->validate(ProjectAdminForm::validationRules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $budgetErrors = ProjectAdminForm::validateBudgetPost($this->request);
        if ($budgetErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $budgetErrors);
        }

        $slug = LocaleSlug::normalizeSlug((string) $this->request->getPost('slug'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        $locale = LocaleSlug::normalizeLocale((string) $this->request->getPost('locale'));
        $budgetPayload = ProjectAdminForm::budgetPayloadFromPost($this->request, $locale);
        $geoPayload    = ProjectGeographyPayload::fromRequest($this->request);

        $model = model(ProjectProjectModel::class);
        if ($model->where('slug', $slug)->where('locale', $locale)->first() !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug est déjà utilisé pour cette langue.');
        }

        $bodyPayload = ProjectAdminForm::resolveBodyPayload($this->request,null);
        if ($bodyPayload === null) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins un bloc valide.');
        }

        $merged        = ProjectBudgetTableSync::applyToPayloads($bodyPayload, $budgetPayload, $locale);
        $bodyPayload   = $merged['body'];
        $budgetPayload = $merged['budget'];

        $pubState = (string) $this->request->getPost('publication_state');
        $publishedAt = $pubState === ProjectProjectModel::PUBLICATION_PUBLISHED
            ? date('Y-m-d H:i:s')
            : null;

        $tgIn = trim((string) $this->request->getPost('translation_group'));

        $model->insert([
            'slug'               => $slug,
            'locale'             => $locale,
            'translation_group'  => $tgIn === '' ? null : $tgIn,
            'title'              => trim((string) $this->request->getPost('title')),
            'excerpt'            => ProjectAdminForm::nullableString($this->request, 'excerpt'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'project_status'     => (string) $this->request->getPost('project_status'),
            'publication_state'  => $pubState,
            'sectors_csv'        => ProjectAdminForm::sectorsCsvFromPost($this->request),
            'volunteers_count'   => max(0, (int) $this->request->getPost('volunteers_count')),
            'budget_display'     => $budgetPayload['budget_display'],
            'budget_amount'      => $budgetPayload['budget_amount'],
            'budget_scale'       => $budgetPayload['budget_scale'],
            'budget_ariary'      => $budgetPayload['budget_ariary'],
            'geography'          => $geoPayload['geography'],
            'geography_data'     => $geoPayload['geography_data'],
            'launched_at'        => ProjectAdminForm::nullableDate($this->request, 'launched_at'),
            'duration_months'    => ProjectAdminForm::nullableUInt($this->request, 'duration_months'),
            'progress_percent'   => ProjectAdminForm::nullableProgress($this->request, 'progress_percent'),
            'meta_title'         => ProjectAdminForm::nullableString($this->request, 'meta_title'),
            'meta_description'   => ProjectAdminForm::nullableString($this->request, 'meta_description'),
            'published_at'       => $publishedAt,
        ]);

        $newId = (int) $model->getInsertID();
        if ($newId > 0) {
            $tgFinal = $tgIn !== '' ? $tgIn : (string) $newId;
            $model->update($newId, ['translation_group' => $tgFinal]);
        }

        return $this->adminRedirectToEdit('admin/project-projects', $newId, 'Projet créé.');
    }

    public function edit(int $id): string
    {
        $project = model(ProjectProjectModel::class)->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $formData = ProjectAdminForm::formViewData($project);

        return view('admin/layout', [
            'title'         => 'Modifier le projet',
            'main'          => view('admin/project_projects/form', $formData),
            'extraScripts'  => ProjectAdminForm::editorScripts($formData),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(ProjectProjectModel::class);
        $project = $model->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate(ProjectAdminForm::validationRules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $budgetErrors = ProjectAdminForm::validateBudgetPost($this->request);
        if ($budgetErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $budgetErrors);
        }

        $slug = LocaleSlug::normalizeSlug((string) $this->request->getPost('slug'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        $locale = LocaleSlug::normalizeLocale((string) ($project['locale'] ?? 'fr'));
        $budgetPayload = ProjectAdminForm::budgetPayloadFromPost($this->request, $locale);
        $geoPayload    = ProjectGeographyPayload::fromRequest($this->request);

        $other = $model->where('slug', $slug)->where('locale', $locale)->where('id !=', $id)->first();
        if ($other !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug est déjà utilisé pour cette langue.');
        }

        $bodyPayload = ProjectAdminForm::resolveBodyPayload($this->request,$project);
        if ($bodyPayload === null) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins un bloc valide.');
        }

        $merged        = ProjectBudgetTableSync::applyToPayloads($bodyPayload, $budgetPayload, $locale);
        $bodyPayload   = $merged['body'];
        $budgetPayload = $merged['budget'];

        $pubState = (string) $this->request->getPost('publication_state');
        $publishedAt = $project['published_at'] ?? null;
        if ($pubState === ProjectProjectModel::PUBLICATION_PUBLISHED && ($publishedAt === null || $publishedAt === '')) {
            $publishedAt = date('Y-m-d H:i:s');
        }
        if ($pubState === ProjectProjectModel::PUBLICATION_DRAFT) {
            $publishedAt = null;
        }

        $tgIn = trim((string) $this->request->getPost('translation_group'));
        if ($tgIn === '') {
            $tgIn = trim((string) ($project['translation_group'] ?? ''));
        }
        if ($tgIn === '') {
            $tgIn = (string) $id;
        }

        $model->update($id, [
            'slug'               => $slug,
            'translation_group'  => $tgIn,
            'title'              => trim((string) $this->request->getPost('title')),
            'excerpt'            => ProjectAdminForm::nullableString($this->request, 'excerpt'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'project_status'     => (string) $this->request->getPost('project_status'),
            'publication_state'  => $pubState,
            'sectors_csv'        => ProjectAdminForm::sectorsCsvFromPost($this->request),
            'volunteers_count'   => max(0, (int) $this->request->getPost('volunteers_count')),
            'budget_display'     => $budgetPayload['budget_display'],
            'budget_amount'      => $budgetPayload['budget_amount'],
            'budget_scale'       => $budgetPayload['budget_scale'],
            'budget_ariary'      => $budgetPayload['budget_ariary'],
            'geography'          => $geoPayload['geography'],
            'geography_data'     => $geoPayload['geography_data'],
            'launched_at'        => ProjectAdminForm::nullableDate($this->request, 'launched_at'),
            'duration_months'    => ProjectAdminForm::nullableUInt($this->request, 'duration_months'),
            'progress_percent'   => ProjectAdminForm::nullableProgress($this->request, 'progress_percent'),
            'meta_title'         => ProjectAdminForm::nullableString($this->request, 'meta_title'),
            'meta_description'   => ProjectAdminForm::nullableString($this->request, 'meta_description'),
            'published_at'       => $publishedAt,
        ]);

        return $this->adminRedirectToEdit('admin/project-projects', $id, 'Projet mis à jour.');
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(ProjectProjectModel::class);
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete($id);

        return redirect()->to(site_url('admin/project-projects'))->with('message', 'Projet supprimé.');
    }

    public function duplicate(int $id): ResponseInterface
    {
        helper('locale');
        $model = model(ProjectProjectModel::class);
        $src   = $model->find($id);
        if ($src === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $srcLocale    = LocaleSlug::normalizeLocale((string) ($src['locale'] ?? 'fr'));
        $targetLocale = $srcLocale === 'fr' ? 'en' : 'fr';

        $srcSlug = LocaleSlug::normalizeSlug((string) ($src['slug'] ?? ''));
        if ($srcSlug === '') {
            $srcSlug = 'projet';
        }

        $baseTargetSlug = $srcLocale === 'fr'
            ? locale_slug_fr_to_en($srcSlug)
            : locale_slug_en_to_fr($srcSlug);
        $baseTargetSlug = LocaleSlug::normalizeSlug($baseTargetSlug);
        if ($baseTargetSlug === '') {
            $baseTargetSlug = 'projet-' . $targetLocale;
        }

        $targetSlug = ProjectAdminForm::uniqueSlugForLocale($baseTargetSlug, $targetLocale, $model);

        $sourceGroup = trim((string) ($src['translation_group'] ?? ''));
        $group       = $sourceGroup !== '' ? $sourceGroup : (string) $id;
        if ($sourceGroup === '') {
            $model->update($id, ['translation_group' => $group]);
        }

        $partner = $model->where('translation_group', $group)->where('locale', $targetLocale)->first();
        if ($partner !== null) {
            return redirect()->to(site_url('admin/project-projects'))
                ->with('error', 'Une variante existe déjà pour cette langue dans ce groupe de traduction.');
        }

        $titleBase = trim((string) ($src['title'] ?? 'Sans titre'));
        $suffix    = $targetLocale === 'en' ? ' (EN)' : ' (FR)';
        if (mb_strlen($titleBase . $suffix) > 255) {
            $titleBase = mb_substr($titleBase, 0, max(1, 255 - mb_strlen($suffix)));
        }

        $model->insert([
            'slug'               => $targetSlug,
            'locale'             => $targetLocale,
            'translation_group'  => $group,
            'title'              => $titleBase . $suffix,
            'excerpt'            => $src['excerpt'] ?? null,
            'body'               => $src['body'] ?? null,
            'body_content_mode'  => (string) ($src['body_content_mode'] ?? 'html'),
            'body_blocks'        => $src['body_blocks'] ?? null,
            'project_status'     => (string) ($src['project_status'] ?? ProjectProjectModel::STATUS_CANDIDAT),
            'publication_state'  => ProjectProjectModel::PUBLICATION_DRAFT,
            'sectors_csv'        => (string) ($src['sectors_csv'] ?? ''),
            'volunteers_count'   => (int) ($src['volunteers_count'] ?? 0),
            'budget_display'     => $src['budget_display'] ?? null,
            'budget_amount'      => $src['budget_amount'] ?? null,
            'budget_scale'       => $src['budget_scale'] ?? null,
            'budget_ariary'      => $src['budget_ariary'] ?? null,
            'geography'          => $src['geography'] ?? null,
            'geography_data'     => $src['geography_data'] ?? null,
            'launched_at'        => $src['launched_at'] ?? null,
            'duration_months'    => $src['duration_months'] ?? null,
            'progress_percent'   => $src['progress_percent'] ?? null,
            'meta_title'         => $src['meta_title'] ?? null,
            'meta_description'   => $src['meta_description'] ?? null,
            'published_at'       => null,
        ]);

        $newId = (int) $model->getInsertID();

        return redirect()
            ->to(site_url('admin/project-projects/edit/' . $newId))
            ->with('message', 'Traduction créée en ' . strtoupper($targetLocale) . ' (brouillon).');
    }
}
