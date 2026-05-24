<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\SiteContext;
use CodeIgniter\Model;

class CmsPostModel extends Model
{
    protected $table            = 'cms_posts';
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
        'excerpt',
        'body_html',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'created_at',
        'updated_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /**
     * @return list<array<string, mixed>>
     */
    public function listPublishedNewestFirst(int $limit = 50, ?string $locale = null): array
    {
        $locale ??= SiteContext::locale();

        return $this->where('status', 'published')
            ->where('locale', $locale)
            ->orderBy('published_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getPublishedBySlug(string $slug, ?string $locale = null): ?array
    {
        $locale ??= SiteContext::locale();

        $row = $this->where('slug', $slug)
            ->where('locale', $locale)
            ->where('status', 'published')
            ->first();

        return $row ?: null;
    }
}
