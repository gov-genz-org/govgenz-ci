<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProjectProjectModel;
use App\Models\SectorModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Sectors extends BaseController
{
    public function index()
    {
        $list = $this->adminPaginatedList(
            model(SectorModel::class),
            [
                'code'          => 'code',
                'label_fr'      => 'label_fr',
                'contact_email' => 'contact_email',
                'sort_order'    => 'sort_order',
                'is_active'     => 'is_active',
            ],
            'sort_order',
            'asc',
            [],
            null,
            'code',
            'ASC',
        );

        return view('admin/layout', [
            'title' => 'Secteurs',
            'main'  => view('admin/sectors/index', [
                'rows'  => $list['rows'],
                'pager' => $list['pager'],
                'sort'  => $list['sort'],
                'dir'   => $list['dir'],
            ]),
        ]);
    }

    public function create()
    {
        $last = model(SectorModel::class)->orderBy('sort_order', 'DESC')->orderBy('id', 'DESC')->first();
        $nextOrder = (int) ($last['sort_order'] ?? 0) + 10;
        if ($nextOrder < 10) {
            $nextOrder = 10;
        }

        return view('admin/layout', [
            'title' => 'Secteurs — nouveau',
            'main'  => view('admin/sectors/form', [
                'sector'    => null,
                'nextOrder' => $nextOrder,
            ]),
        ]);
    }

    public function store(): ResponseInterface
    {
        $errors = $this->validateSectorPayload(null, null);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $payload = $this->payloadFromPost();
        $model   = model(SectorModel::class);
        $model->insert($payload);

        return $this->adminRedirectToEdit('admin/sectors', (int) $model->getInsertID(), 'Secteur créé.');
    }

    public function edit(int $id)
    {
        $row = model(SectorModel::class)->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/layout', [
            'title' => 'Secteurs — modifier',
            'main'  => view('admin/sectors/form', [
                'sector'    => $row,
                'nextOrder' => null,
            ]),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(SectorModel::class);
        $row   = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $errors = $this->validateSectorPayload($id, $row);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $payload         = $this->payloadFromPost();
        $payload['code'] = strtolower(trim((string) ($row['code'] ?? '')));

        $model->update($id, $payload);

        return $this->adminRedirectToEdit('admin/sectors', $id, 'Secteur mis à jour.');
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(SectorModel::class);
        $row   = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $code = strtolower(trim((string) ($row['code'] ?? '')));
        if ($code !== '' && $this->sectorCodeUsedInProjects($code)) {
            return redirect()->to(site_url('admin/sectors'))->with(
                'error',
                'Impossible de supprimer : ce secteur est encore associé à un ou plusieurs projets. Retirez-le des projets puis réessayez.',
            );
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/sectors'))->with('message', lang('Admin.flash_sector_deleted'));
    }

    /**
     * @param array<string, mixed>|null $existingRow
     * @return array<string, string>
     */
    private function validateSectorPayload(?int $existingId, ?array $existingRow = null): array
    {
        $errors = [];

        $code = strtolower(trim((string) $this->request->getPost('code')));
        if ($existingId !== null && $existingRow !== null) {
            $expected = strtolower(trim((string) ($existingRow['code'] ?? '')));
            if ($code !== $expected) {
                $errors['code'] = 'Le code technique ne peut pas être modifié (stables pour les candidatures et projets).';
            }
        } else {
            if ($code === '') {
                $errors['code'] = 'Le code technique est obligatoire.';
            } elseif (! preg_match('/^[a-z][a-z0-9_-]{0,30}$/', $code)) {
                $errors['code'] = 'Code : lettre minuscule puis minuscules, chiffres, tirets ou underscores (max. 32 caractères).';
            } else {
                $other = model(SectorModel::class)->where('code', $code)->first();
                if ($other !== null) {
                    $errors['code'] = 'Ce code est déjà utilisé.';
                }
            }
        }

        $labelFr = trim((string) $this->request->getPost('label_fr'));
        if ($labelFr === '') {
            $errors['label_fr'] = 'Le libellé français est obligatoire.';
        } elseif (mb_strlen($labelFr) > 255) {
            $errors['label_fr'] = 'Libellé FR trop long (255 max).';
        }

        $labelEn = trim((string) $this->request->getPost('label_en'));
        if ($labelEn === '') {
            $errors['label_en'] = 'The English label is required. / Le libellé anglais est obligatoire.';
        } elseif (mb_strlen($labelEn) > 255) {
            $errors['label_en'] = 'English label too long (255 max).';
        }

        $codeFr = trim((string) $this->request->getPost('code_fr'));
        if ($codeFr !== '') {
            if (mb_strlen($codeFr) > 48) {
                $errors['code_fr'] = 'Code filtre FR trop long (48 max).';
            } elseif (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,47}$/', $codeFr)) {
                $errors['code_fr'] = 'Code filtre : lettre ou chiffre en tête, puis lettres, chiffres, tirets ou underscores (ex. Education, Food).';
            }
        }

        $codeEn = trim((string) $this->request->getPost('code_en'));
        if ($codeEn !== '') {
            if (mb_strlen($codeEn) > 48) {
                $errors['code_en'] = 'Code filtre EN trop long (48 max).';
            } elseif (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,47}$/', $codeEn)) {
                $errors['code_en'] = 'Filter code (EN): leading letter or digit, then letters, digits, hyphens or underscores.';
            }
        }

        $email = trim((string) $this->request->getPost('contact_email'));
        if ($email === '') {
            $errors['contact_email'] = 'L’e-mail de contact est obligatoire.';
        } elseif (mb_strlen($email) > 190 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = 'Adresse e-mail invalide.';
        }

        $sortRaw = $this->request->getPost('sort_order');
        $sort    = is_numeric($sortRaw) ? (int) $sortRaw : -1;
        if ($sort < 0 || $sort > 32767) {
            $errors['sort_order'] = 'Ordre invalide (0 à 32767).';
        }

        return $errors;
    }

    /**
     * @return array<string, int|string|null>
     */
    private function payloadFromPost(): array
    {
        $code = strtolower(trim((string) $this->request->getPost('code')));

        $activeRaw = $this->request->getPost('is_active');
        $active    = (string) $activeRaw === '1' || $activeRaw === 1 || $activeRaw === true ? 1 : 0;

        $codeFr = trim((string) $this->request->getPost('code_fr'));
        $codeEn = trim((string) $this->request->getPost('code_en'));

        return [
            'code'            => $code,
            'code_fr'         => $codeFr === '' ? null : $codeFr,
            'code_en'         => $codeEn === '' ? null : $codeEn,
            'label_fr'        => trim((string) $this->request->getPost('label_fr')),
            'label_en'        => trim((string) $this->request->getPost('label_en')),
            'contact_email'   => trim((string) $this->request->getPost('contact_email')),
            'sort_order'      => (int) $this->request->getPost('sort_order'),
            'is_active'       => $active,
        ];
    }

    private function sectorCodeUsedInProjects(string $code): bool
    {
        $projects = model(ProjectProjectModel::class)->select('sectors_csv')->findAll();
        foreach ($projects as $p) {
            $parts = array_map('strtolower', array_map('trim', explode(',', (string) ($p['sectors_csv'] ?? ''))));
            if (in_array($code, $parts, true)) {
                return true;
            }
        }

        return false;
    }
}
