<?php

declare(strict_types=1);

namespace App\Models;

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
