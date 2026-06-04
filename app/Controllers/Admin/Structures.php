<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StructureUnitModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Structures extends BaseController
{
    public function index()
    {
        $model = model(StructureUnitModel::class);

        $activeRaw = $this->request->getGet('active');
        $filterActive = 'all';
        if ($activeRaw === '1' || $activeRaw === 1) {
            $filterActive = '1';
            $model = $model->where('is_active', 1);
        } elseif ($activeRaw === '0' || $activeRaw === 0) {
            $filterActive = '0';
            $model = $model->where('is_active', 0);
        }

        $searchQuery = trim((string) $this->request->getGet('q'));
        if ($searchQuery !== '') {
            if (mb_strlen($searchQuery) > 120) {
                $searchQuery = mb_substr($searchQuery, 0, 120);
            }
            $model = $model->groupStart()
                ->like('code', $searchQuery)
                ->orLike('title_fr', $searchQuery)
                ->orLike('title_en', $searchQuery)
                ->orLike('contact_email', $searchQuery)
                ->groupEnd();
        }

        $list = $this->adminPaginatedList(
            $model,
            [
                'code'          => 'code',
                'title_fr'      => 'title_fr',
                'contact_email' => 'contact_email',
                'sort_order'    => 'sort_order',
                'is_active'     => 'is_active',
            ],
            'sort_order',
            'asc',
            ['active', 'q'],
            null,
            'id',
            'ASC',
        );

        $sort = $list['sort'];
        $dir  = $list['dir'];
        $rows = $list['rows'];
        $pager = $list['pager'];
        $enableSortableList = $searchQuery === ''
            && $sort === 'sort_order'
            && strtolower($dir) === 'asc'
            && $filterActive !== '0';

        if ($enableSortableList) {
            $rows = $model->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll();
            $pager = null;
        }

        $extraHead = '';
        if ($enableSortableList) {
            $extraHead = '<script defer src="' . esc(base_url('js/admin/admin-sortable-list.js'), 'attr') . '"></script>';
        }

        return view('admin/layout', [
            'title'     => lang('Admin.title_structures'),
            'extraHead' => $extraHead,
            'main'      => view('admin/structures/index', [
                'rows'               => $rows,
                'pager'              => $pager,
                'sort'               => $sort,
                'dir'                => $dir,
                'filterActive'       => $filterActive,
                'searchQuery'        => $searchQuery,
                'enableSortableList' => $enableSortableList,
                'reorderUrl'         => site_url('admin/structures/reorder'),
            ]),
        ]);
    }

    public function reorder(): ResponseInterface
    {
        $payload = $this->adminReorderSortOrderPayload();
        if ($payload['ok'] !== true) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok'      => false,
                'message' => $payload['message'] ?? lang('Admin.error_reorder_empty'),
            ]);
        }

        $this->adminApplySortOrderFromIds(model(StructureUnitModel::class), $payload['ids']);

        return $this->response->setJSON(['ok' => true]);
    }

    public function create()
    {
        $last = model(StructureUnitModel::class)->orderBy('sort_order', 'DESC')->orderBy('id', 'DESC')->first();
        $nextOrder = (int) ($last['sort_order'] ?? 0) + 10;
        if ($nextOrder < 10) {
            $nextOrder = 10;
        }

        return view('admin/layout', [
            'title'        => lang('Admin.form_structure_new'),
            'extraScripts' => $this->adminMediaSlotScripts(),
            'main'         => view('admin/structures/form', [
                'unit'      => null,
                'nextOrder' => $nextOrder,
            ]),
        ]);
    }

    public function store(): ResponseInterface
    {
        $errors = $this->validatePayload(null);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $model = model(StructureUnitModel::class);
        $model->insert($this->payloadFromPost());

        return $this->adminRedirectToEdit('admin/structures', (int) $model->getInsertID(), lang('Admin.flash_structure_created'));
    }

    public function edit(int $id)
    {
        $row = model(StructureUnitModel::class)->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/layout', [
            'title'        => lang('Admin.form_structure_edit'),
            'extraScripts' => $this->adminMediaSlotScripts(),
            'main'         => view('admin/structures/form', [
                'unit'      => $row,
                'nextOrder' => null,
            ]),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(StructureUnitModel::class);
        $row   = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $errors = $this->validatePayload($id);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $payload         = $this->payloadFromPost();
        $payload['code'] = strtolower(trim((string) ($row['code'] ?? '')));
        $model->update($id, $payload);

        return $this->adminRedirectToEdit('admin/structures', $id, lang('Admin.flash_structure_updated'));
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(StructureUnitModel::class);
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/structures'))->with('message', lang('Admin.flash_structure_deleted'));
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(?int $existingId): array
    {
        $errors = [];
        $code = strtolower(trim((string) $this->request->getPost('code')));
        $titleFr = trim((string) $this->request->getPost('title_fr'));
        $titleEn = trim((string) $this->request->getPost('title_en'));
        $email = trim((string) $this->request->getPost('contact_email'));

        if ($existingId === null) {
            if ($code === '' || ! preg_match('/^[a-z][a-z0-9_-]{0,30}$/', $code)) {
                $errors['code'] = lang('Admin.error_structure_code');
            } elseif (model(StructureUnitModel::class)->where('code', $code)->first() !== null) {
                $errors['code'] = lang('Admin.error_structure_code_taken');
            }
        }

        if ($titleFr === '') {
            $errors['title_fr'] = lang('Admin.error_structure_title_fr');
        }
        if ($titleEn === '') {
            $errors['title_en'] = lang('Admin.error_structure_title_en');
        }
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = lang('Admin.error_structure_email');
        }

        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromPost(): array
    {
        return [
            'code'            => strtolower(trim((string) $this->request->getPost('code'))),
            'unit_role'       => strtolower(trim((string) $this->request->getPost('unit_role'))) === 'core' ? 'core' : 'function',
            'title_fr'        => trim((string) $this->request->getPost('title_fr')),
            'title_en'        => trim((string) $this->request->getPost('title_en')),
            'subtitle_fr'     => trim((string) $this->request->getPost('subtitle_fr')) ?: null,
            'subtitle_en'     => trim((string) $this->request->getPost('subtitle_en')) ?: null,
            'description_fr'  => trim((string) $this->request->getPost('description_fr')) ?: null,
            'description_en'  => trim((string) $this->request->getPost('description_en')) ?: null,
            'contact_email'   => trim((string) $this->request->getPost('contact_email')),
            'media_id'        => max(0, (int) $this->request->getPost('media_id')) ?: null,
            'media_alt'       => trim((string) $this->request->getPost('media_alt')) ?: null,
            'sort_order'      => max(0, (int) $this->request->getPost('sort_order')),
            'is_active'       => (int) $this->request->getPost('is_active') === 1 ? 1 : 0,
        ];
    }
}
