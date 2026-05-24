<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiteNavItemModel;
use CodeIgniter\HTTP\ResponseInterface;

class SiteMenu extends BaseController
{
    public function index()
    {
        $list = $this->adminPaginatedList(
            model(SiteNavItemModel::class),
            [
                'locale'     => 'locale',
                'sort_order' => 'sort_order',
                'label'      => 'label',
                'href_kind'  => 'href_kind',
                'is_active'  => 'is_active',
            ],
            'locale',
            'asc',
            [],
            null,
            'sort_order',
            'ASC',
        );

        return view('admin/layout', [
            'title' => lang('Admin.nav_site_menu'),
            'main'  => view('admin/site_menu/index', [
                'items' => $list['rows'],
                'pager' => $list['pager'],
                'sort'  => $list['sort'],
                'dir'   => $list['dir'],
            ]),
        ]);
    }

    public function create()
    {
        return view('admin/layout', [
            'title' => lang('Admin.title_site_menu_create'),
            'main'  => view('admin/site_menu/form', ['item' => null]),
        ]);
    }

    public function store(): ResponseInterface
    {
        $errors = $this->collectNavErrors();
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $model = model(SiteNavItemModel::class);
        $model->insert($this->payloadFromPost());

        return $this->adminRedirectToEdit('admin/site-menu', (int) $model->getInsertID(), lang('Admin.flash_menu_entry_created'));
    }

    public function edit(int $id)
    {
        $model = model(SiteNavItemModel::class);
        $item  = $model->find($id);
        if ($item === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/layout', [
            'title' => lang('Admin.title_site_menu_edit'),
            'main'  => view('admin/site_menu/form', ['item' => $item]),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(SiteNavItemModel::class);
        if ($model->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $errors = $this->collectNavErrors();
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $model->update($id, $this->payloadFromPost());

        return $this->adminRedirectToEdit('admin/site-menu', $id, lang('Admin.flash_menu_entry_updated'));
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(SiteNavItemModel::class);
        if ($model->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/site-menu'))->with('message', lang('Admin.flash_menu_entry_deleted'));
    }

    /**
     * @return array<string, string>
     */
    private function collectNavErrors(): array
    {
        $errors = [];

        $label = trim((string) $this->request->getPost('label'));
        if ($label === '') {
            $errors['label'] = lang('Admin.error_sitemenu_label_required');
        } elseif (mb_strlen($label) > 255) {
            $errors['label'] = lang('Admin.error_sitemenu_label_too_long');
        }

        $locale = strtolower(trim((string) $this->request->getPost('locale')));
        if (! in_array($locale, ['fr', 'en'], true)) {
            $errors['locale'] = lang('Admin.error_sitemenu_locale_invalid');
        }

        $kind = strtolower(trim((string) $this->request->getPost('href_kind')));
        if (! in_array($kind, ['home', 'segment', 'path', 'external'], true)) {
            $errors['href_kind'] = lang('Admin.error_sitemenu_href_kind');
        }

        $mk = strtolower(trim((string) $this->request->getPost('match_key')));
        if ($mk === '') {
            $errors['match_key'] = lang('Admin.error_sitemenu_match_key_required');
        } elseif (! preg_match('/^[a-z0-9_-]+$/', $mk)) {
            $errors['match_key'] = lang('Admin.error_sitemenu_match_key_format');
        }

        $target = trim((string) $this->request->getPost('href_target'));

        if ($kind === 'home') {
            // ok
        } elseif ($kind === 'external') {
            if ($target === '') {
                $errors['href_target'] = lang('Admin.error_sitemenu_external_url_required');
            } elseif (! preg_match('#^https?://#i', $target)) {
                $errors['href_target'] = lang('Admin.error_sitemenu_external_url_scheme');
            } elseif (mb_strlen($target) > 512) {
                $errors['href_target'] = lang('Admin.error_sitemenu_url_too_long');
            }
        } elseif ($target === '') {
            $errors['href_target'] = lang('Admin.error_sitemenu_target_required');
        } else {
            $targetNorm = strtolower(trim($target, '/'));
            if ($kind === 'segment') {
                if (! preg_match('/^[a-z0-9-]+$/', $targetNorm)) {
                    $errors['href_target'] = lang('Admin.error_sitemenu_segment_format');
                }
            } elseif ($kind === 'path') {
                if (! preg_match('#^[a-z0-9]+(?:/[a-z0-9-]+)*$#', $targetNorm)) {
                    $errors['href_target'] = lang('Admin.error_sitemenu_path_format');
                }
            }
        }

        $css = trim((string) $this->request->getPost('css_class'));
        if ($css !== '' && ! preg_match('/^[a-zA-Z0-9_-]+(?:\s+[a-zA-Z0-9_-]+)*$/', $css)) {
            $errors['css_class'] = lang('Admin.error_sitemenu_css_class_format');
        }

        $sortRaw = $this->request->getPost('sort_order');
        if ($sortRaw !== null && $sortRaw !== '' && ! is_numeric($sortRaw)) {
            $errors['sort_order'] = lang('Admin.error_sitemenu_sort_order');
        }

        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromPost(): array
    {
        $kind   = strtolower(trim((string) $this->request->getPost('href_kind')));
        $target = trim((string) $this->request->getPost('href_target'));
        $mk     = strtolower(trim((string) $this->request->getPost('match_key')));

        if ($kind === 'home') {
            $hrefTarget = null;
        } elseif ($kind === 'external') {
            $hrefTarget = $target;
        } else {
            $hrefTarget = strtolower(trim($target, '/'));
        }

        $sort = (int) $this->request->getPost('sort_order');
        if ($sort < 0) {
            $sort = 0;
        }

        $css = trim((string) $this->request->getPost('css_class'));

        $locale = strtolower(trim((string) $this->request->getPost('locale')));
        if (! in_array($locale, ['fr', 'en'], true)) {
            $locale = 'fr';
        }

        return [
            'locale'       => $locale,
            'sort_order'   => $sort,
            'label'        => trim((string) $this->request->getPost('label')),
            'href_kind'    => $kind,
            'href_target'  => $hrefTarget,
            'match_key'    => $mk,
            'css_class'    => $css !== '' ? $css : null,
            'is_active'    => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ];
    }
}
