<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CmsMediaStorage;
use App\Models\CmsMediaModel;
use App\Models\CmsPageModel;
use App\Models\CmsPostModel;
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
            $displayName = $this->mediaDisplayName($r);
            $out[] = [
                'id'     => (int) ($r['id'] ?? 0),
                'url'    => CmsMediaStorage::publicUrl($fn),
                'name'   => $displayName,
                'mime'   => (string) ($r['mime_type'] ?? ''),
                'exists' => CmsMediaStorage::fileExists($fn),
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
        $list = $this->adminPaginatedList(
            model(CmsMediaModel::class),
            [
                'id'            => 'id',
                'original_name' => 'original_name',
                'size_bytes'    => 'size_bytes',
                'created_at'    => 'created_at',
            ],
            'id',
            'desc',
            [],
            self::INDEX_PAGE_SIZE,
        );
        $total = (int) $list['pager']->getTotal('default');

        return view('admin/layout', [
            'title'      => 'Médias',
            'extraHead'  => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5/dist/min/dropzone.min.css">',
            'main'       => view('admin/media/index', [
                'items'      => $list['rows'],
                'pager'      => $list['pager'],
                'sort'       => $list['sort'],
                'dir'        => $list['dir'],
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

        $uploadDir = CmsMediaStorage::storageDir();
        if (! CmsMediaStorage::ensureStorageDir()) {
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

        $publicUrl = CmsMediaStorage::publicUrl($newName);

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
            return redirect()->to(site_url('admin/media'))->with('error', lang('Admin.error_media_not_found'));
        }

        $storedFilename = (string) ($row['stored_filename'] ?? '');
        $fileExists     = CmsMediaStorage::fileExists($storedFilename);
        if ($fileExists && $this->mediaIsReferenced($row)) {
            return redirect()->to(site_url('admin/media'))->with('error', lang('Admin.error_media_in_use'));
        }

        $path = CmsMediaStorage::resolveReadablePath($storedFilename);
        if (is_file($path)) {
            @unlink($path);
        }

        $model->delete($id);

        return redirect()->to(site_url('admin/media'))->with('message', lang('Admin.flash_media_deleted'));
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

    /**
     * @param array<string, mixed> $media
     */
    private function mediaIsReferenced(array $media): bool
    {
        $id = (int) ($media['id'] ?? 0);
        $fn = basename((string) ($media['stored_filename'] ?? ''));
        if ($id <= 0 && $fn === '') {
            return false;
        }

        foreach (model(CmsPageModel::class)->findAll() as $page) {
            if ($id > 0 && (int) ($page['hero_image_id'] ?? 0) === $id) {
                return true;
            }
            if ($fn !== '' && (
                $this->htmlReferencesMedia((string) ($page['body_html'] ?? ''), $fn)
                || $this->htmlReferencesMedia((string) ($page['body_blocks'] ?? ''), $fn)
            )) {
                return true;
            }
            if ($id > 0 && $this->jsonReferencesMediaId((string) ($page['body_blocks'] ?? ''), $id)) {
                return true;
            }
        }

        if ($fn === '') {
            return false;
        }

        foreach (model(CmsPostModel::class)->findAll() as $post) {
            if (
                $this->htmlReferencesMedia((string) ($post['body_html'] ?? ''), $fn)
                || $this->htmlReferencesMedia((string) ($post['excerpt'] ?? ''), $fn)
            ) {
                return true;
            }
        }

        return false;
    }

    private function htmlReferencesMedia(string $html, string $storedFilename): bool
    {
        if ($html === '' || $storedFilename === '') {
            return false;
        }

        return str_contains($html, 'uploads/cms/' . $storedFilename)
            || str_contains($html, '/uploads/cms/' . $storedFilename);
    }

    private function jsonReferencesMediaId(string $json, int $mediaId): bool
    {
        $json = trim($json);
        if ($json === '' || $json === '[]') {
            return false;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return false;
        }

        return $this->valueReferencesMediaId($decoded, $mediaId);
    }

    /**
     * @param mixed $value
     */
    private function valueReferencesMediaId(mixed $value, int $mediaId): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $key => $child) {
            if (is_string($key) && preg_match('/(^|_)media_id(_\d+)?$/', $key) === 1 && (int) $child === $mediaId) {
                return true;
            }
            if (is_array($child) && $this->valueReferencesMediaId($child, $mediaId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mediaDisplayName(array $row): string
    {
        $name = trim((string) ($row['original_name'] ?? ''));
        if ($name !== '') {
            return basename($name);
        }

        return basename((string) ($row['stored_filename'] ?? ''));
    }
}
