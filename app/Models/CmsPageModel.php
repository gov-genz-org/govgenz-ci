<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\CmsPublishedPageCache;
use App\Libraries\SiteContext;
use CodeIgniter\Model;

class CmsPageModel extends Model
{
    protected $table            = 'cms_pages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'locale',
        'translation_group',
        'title',
        'body_html',
        'content_mode',
        'body_blocks',
        'status',
        'meta_title',
        'meta_description',
        'layout_key',
        'hero_overline',
        'hero_title',
        'hero_lead',
        'hero_image_id',
        'hero_image_alt',
        'created_at',
        'updated_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /** @var ?array<string, mixed> */
    private ?array $pageCacheRowBefore = null;

    protected $afterInsert = ['invalidatePublishedPageCache'];
    protected $beforeUpdate = ['rememberPublishedPageCacheRow'];
    protected $afterUpdate  = ['invalidatePublishedPageCache'];
    protected $beforeDelete = ['rememberPublishedPageCacheRow'];
    protected $afterDelete  = ['invalidatePublishedPageCache'];

    public function getPublishedBySlug(string $slug, ?string $locale = null): ?array
    {
        $locale ??= SiteContext::locale();

        return CmsPublishedPageCache::remember($locale, $slug, function () use ($slug, $locale): ?array {
            $row = $this->where('slug', $slug)
                ->where('locale', $locale)
                ->where('status', 'published')
                ->first();

            return $row ?: null;
        });
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function rememberPublishedPageCacheRow(array $data): array
    {
        $idRaw = $data['id'] ?? $data[$this->primaryKey] ?? 0;
        if (is_array($idRaw)) {
            $idRaw = $idRaw[0] ?? 0;
        }
        $id = (int) $idRaw;
        if ($id <= 0) {
            $this->pageCacheRowBefore = null;

            return $data;
        }

        $this->pageCacheRowBefore = $this->find($id);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function invalidatePublishedPageCache(array $data): void
    {
        $before = $this->pageCacheRowBefore;
        $this->pageCacheRowBefore = null;

        if ($before !== null) {
            CmsPublishedPageCache::forgetRow($before);
        }

        $id = (int) ($data['id'] ?? $data[$this->primaryKey] ?? 0);
        if ($id <= 0) {
            $id = (int) $this->getInsertID();
        }
        if ($id > 0) {
            $row = $this->find($id);
            if ($row !== null) {
                CmsPublishedPageCache::forgetRow($row);
            }
        }

        $locale = trim((string) ($data['locale'] ?? ''));
        $slug   = trim((string) ($data['slug'] ?? ''));
        if ($locale !== '' && $slug !== '') {
            CmsPublishedPageCache::forget($locale, $slug);
        }
    }
}
