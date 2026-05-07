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
        $rows = model(SiteNavItemModel::class)->orderBy('locale', 'ASC')->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll();

        return view('admin/layout', [
            'title' => 'Menu du site',
            'main'  => view('admin/site_menu/index', ['items' => $rows]),
        ]);
    }

    public function create()
    {
        return view('admin/layout', [
            'title' => 'Menu — nouvelle entrée',
            'main'  => view('admin/site_menu/form', ['item' => null]),
        ]);
    }

    public function store(): ResponseInterface
    {
        $errors = $this->collectNavErrors();
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        model(SiteNavItemModel::class)->insert($this->payloadFromPost());

        return redirect()->to(site_url('admin/site-menu'))->with('message', 'Entrée de menu créée.');
    }

    public function edit(int $id)
    {
        $model = model(SiteNavItemModel::class);
        $item  = $model->find($id);
        if ($item === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/layout', [
            'title' => 'Menu — éditer',
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

        return redirect()->to(site_url('admin/site-menu'))->with('message', 'Entrée de menu mise à jour.');
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(SiteNavItemModel::class);
        if ($model->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/site-menu'))->with('message', 'Entrée supprimée.');
    }

    /**
     * @return array<string, string>
     */
    private function collectNavErrors(): array
    {
        $errors = [];

        $label = trim((string) $this->request->getPost('label'));
        if ($label === '') {
            $errors['label'] = 'Le libellé est obligatoire.';
        } elseif (mb_strlen($label) > 255) {
            $errors['label'] = 'Libellé trop long (255 caractères max).';
        }

        $locale = strtolower(trim((string) $this->request->getPost('locale')));
        if (! in_array($locale, ['fr', 'en'], true)) {
            $errors['locale'] = 'Langue invalide.';
        }

        $kind = strtolower(trim((string) $this->request->getPost('href_kind')));
        if (! in_array($kind, ['home', 'segment', 'path', 'external'], true)) {
            $errors['href_kind'] = 'Type de lien invalide.';
        }

        $mk = strtolower(trim((string) $this->request->getPost('match_key')));
        if ($mk === '') {
            $errors['match_key'] = 'La clé de surlignage est obligatoire (ex. home, press, slug de page).';
        } elseif (! preg_match('/^[a-z0-9_-]+$/', $mk)) {
            $errors['match_key'] = 'Utilisez uniquement lettres minuscules, chiffres, tirets et underscores.';
        }

        $target = trim((string) $this->request->getPost('href_target'));

        if ($kind === 'home') {
            // ok
        } elseif ($kind === 'external') {
            if ($target === '') {
                $errors['href_target'] = 'Une URL complète est requise pour un lien externe.';
            } elseif (! preg_match('#^https?://#i', $target)) {
                $errors['href_target'] = 'L’URL doit commencer par http:// ou https://';
            } elseif (mb_strlen($target) > 512) {
                $errors['href_target'] = 'URL trop longue.';
            }
        } elseif ($target === '') {
            $errors['href_target'] = 'Une cible est requise pour ce type de lien.';
        } else {
            $targetNorm = strtolower(trim($target, '/'));
            if ($kind === 'segment') {
                if (! preg_match('/^[a-z0-9-]+$/', $targetNorm)) {
                    $errors['href_target'] = 'Segment d’URL : minuscules, chiffres et tirets uniquement.';
                }
            } elseif ($kind === 'path') {
                if (! preg_match('#^[a-z0-9]+(?:/[a-z0-9-]+)*$#', $targetNorm)) {
                    $errors['href_target'] = 'Chemin interne invalide (ex. admin/login).';
                }
            }
        }

        $css = trim((string) $this->request->getPost('css_class'));
        if ($css !== '' && ! preg_match('/^[a-zA-Z0-9_-]+(?:\s+[a-zA-Z0-9_-]+)*$/', $css)) {
            $errors['css_class'] = 'Classes CSS : lettres, chiffres, tirets, underscores et espaces uniquement.';
        }

        $sortRaw = $this->request->getPost('sort_order');
        if ($sortRaw !== null && $sortRaw !== '' && ! is_numeric($sortRaw)) {
            $errors['sort_order'] = 'Ordre : nombre entier.';
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
