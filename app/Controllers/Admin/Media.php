<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CmsMediaModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Mimes;

class Media extends BaseController
{
    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'];

    private const INDEX_PAGE_SIZE = 24;

    private const JSON_PAGE_SIZE = 48;

    public function jsonList(): ResponseInterface
    {
        $model = model(CmsMediaModel::class);
        $total = $model->countAllResults();
        $per   = self::JSON_PAGE_SIZE;
        $pages = max(1, (int) ceil($total / $per));
        $page  = max(1, (int) $this->request->getGet('page'));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $per;
        $rows   = $model->orderBy('id', 'DESC')->findAll($per, $offset);
        $out    = [];
        foreach ($rows as $r) {
            $fn = (string) ($r['stored_filename'] ?? '');
            if ($fn === '') {
                continue;
            }
            $out[] = [
                'id'   => (int) ($r['id'] ?? 0),
                'url'  => base_url('uploads/cms/' . $fn),
                'name' => (string) ($r['original_name'] ?? ''),
                'mime' => (string) ($r['mime_type'] ?? ''),
            ];
        }

        return $this->response->setJSON([
            'items'      => $out,
            'page'       => $page,
            'perPage'    => $per,
            'total'      => $total,
            'totalPages' => $pages,
        ]);
    }

    public function index()
    {
        service('pager')->only(['page']);

        $model = model(CmsMediaModel::class);
        $items = $model->orderBy('id', 'DESC')->paginate(self::INDEX_PAGE_SIZE, 'default');
        $total = (int) $model->pager->getTotal('default');

        return view('admin/layout', [
            'title'      => 'Médias',
            'extraHead'  => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5/dist/min/dropzone.min.css">',
            'main'       => view('admin/media/index', [
                'items' => $items,
                'pager' => $model->pager,
                'totalMedia' => $total,
            ]),
            'extraScripts' => view('admin/partials/media_dropzone', [
                'uploadUrl' => site_url('admin/media/upload'),
            ]),
        ]);
    }

    public function upload(): ResponseInterface
    {
        /** @var \CodeIgniter\HTTP\IncomingRequest $request */
        $request = $this->request;
        $file    = $request->getFile('file');

        $fail = static function (string $message) {
            return service('response')
                ->setStatusCode(400)
                ->setJSON(['error' => $message, 'csrfHash' => csrf_hash()]);
        };

        if ($file === null || ! $file->isValid()) {
            return $fail('Fichier invalide ou manquant.');
        }

        if ($file->hasMoved()) {
            return $fail('Fichier déjà traité.');
        }

        $ext = strtolower((string) $file->getExtension());
        if ($ext === '') {
            $guess = $file->guessExtension();
            $ext   = $guess !== false ? strtolower($guess) : '';
        }

        if ($ext === '' || ! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return $fail('Type de fichier non autorisé.');
        }

        $mime = $file->getClientMimeType();
        if (! $this->mimeAllowed($mime, $ext)) {
            return $fail('Type MIME non autorisé.');
        }

        $maxBytes = 10 * 1024 * 1024;
        $sizeBytes = $file->getSize();
        if ($sizeBytes > $maxBytes) {
            return $fail('Fichier trop volumineux (max 10 Mo).');
        }

        $originalName = $file->getClientName();

        $uploadDir = FCPATH . 'uploads/cms/';
        if (! is_dir($uploadDir) && ! mkdir($uploadDir, 0755, true) && ! is_dir($uploadDir)) {
            return $fail('Impossible de créer le dossier d’upload.');
        }

        $newName = $file->getRandomName();
        $file->move($uploadDir, $newName);

        if (! is_file($uploadDir . $newName)) {
            return $fail('Échec de l’enregistrement du fichier.');
        }

        $model = model(CmsMediaModel::class);
        $model->insert([
            'stored_filename' => $newName,
            'original_name'   => $originalName,
            'mime_type'       => (string) $mime,
            'size_bytes'      => $sizeBytes,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $publicUrl = base_url('uploads/cms/' . $newName);

        return $this->response->setJSON([
            'location' => $publicUrl,
            'csrfHash' => csrf_hash(),
            'id'       => $model->getInsertID(),
        ]);
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(CmsMediaModel::class);
        $row   = $model->find($id);
        if ($row === null) {
            return redirect()->to(site_url('admin/media'))->with('error', 'Média introuvable.');
        }

        $path = FCPATH . 'uploads/cms/' . ($row['stored_filename'] ?? '');
        if (is_file($path)) {
            @unlink($path);
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/media'))->with('message', 'Média supprimé.');
    }

    private function mimeAllowed(?string $mime, string $ext): bool
    {
        if ($mime === null || $mime === '') {
            return false;
        }

        if ($ext === 'svg') {
            $svgMimes = ['image/svg+xml', 'image/svg', 'text/plain', 'application/xml', 'text/xml'];
            foreach ($svgMimes as $allowed) {
                if (strcasecmp($mime, $allowed) === 0) {
                    return true;
                }
            }
        }

        $defs = Mimes::$mimes[$ext] ?? null;
        if ($defs === null) {
            return false;
        }

        foreach ((array) $defs as $a) {
            if (strcasecmp($mime, $a) === 0) {
                return true;
            }
        }

        return false;
    }
}
