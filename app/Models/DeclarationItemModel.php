<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\SiteContext;
use CodeIgniter\Model;

class DeclarationItemModel extends Model
{
    public const KIND_OFFICIAL = 'official';

    public const KIND_PLEDGE = 'pledge';

    public const KIND_ALERT = 'alert';

    public const KIND_PARTNERSHIP = 'partnership';

    public const SECTION_DECLARATIONS = 'declarations';

    public const SECTION_PARTNERSHIPS = 'partnerships';

    public const PUBLICATION_DRAFT = 'draft';

    public const PUBLICATION_PUBLISHED = 'published';

    protected $table            = 'declaration_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'locale',
        'translation_group',
        'title',
        'summary',
        'body',
        'body_content_mode',
        'body_blocks',
        'kind',
        'list_section',
        'meta_line',
        'band_label',
        'badge_label',
        'cta_label',
        'cta_href',
        'sort_order',
        'publication_state',
        'published_at',
        'created_at',
        'updated_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /** @return list<string> */
    public static function kindCodes(): array
    {
        return [
            self::KIND_OFFICIAL,
            self::KIND_PLEDGE,
            self::KIND_ALERT,
            self::KIND_PARTNERSHIP,
        ];
    }

    /** @return list<string> */
    public static function listSectionCodes(): array
    {
        return [self::SECTION_DECLARATIONS, self::SECTION_PARTNERSHIPS];
    }

    /** @return array<string, string> */
    public static function publicationStateLabels(): array
    {
        return [
            self::PUBLICATION_DRAFT     => lang('Admin.filter_draft'),
            self::PUBLICATION_PUBLISHED => lang('Admin.filter_published'),
        ];
    }

    /** @return array<string, string> */
    public static function kindLabels(): array
    {
        return [
            self::KIND_OFFICIAL     => lang('Admin.decl_kind_official'),
            self::KIND_PLEDGE       => lang('Admin.decl_kind_pledge'),
            self::KIND_ALERT        => lang('Admin.decl_kind_alert'),
            self::KIND_PARTNERSHIP  => lang('Admin.decl_kind_partnership'),
        ];
    }

    /** @return array<string, string> */
    public static function listSectionLabels(): array
    {
        return [
            self::SECTION_DECLARATIONS  => lang('Admin.decl_section_declarations'),
            self::SECTION_PARTNERSHIPS  => lang('Admin.decl_section_partnerships'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findPublishedBySlug(string $slug, ?string $locale = null): ?array
    {
        $locale ??= SiteContext::locale();
        if ($locale !== 'fr' && $locale !== 'en') {
            $locale = 'fr';
        }

        $slug = strtolower(trim($slug));
        if ($slug === '' || ! preg_match('/^[a-z0-9\-]+$/', $slug)) {
            return null;
        }

        $row = $this->where('slug', $slug)
            ->where('locale', $locale)
            ->where('publication_state', self::PUBLICATION_PUBLISHED)
            ->first();

        return is_array($row) ? $row : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listPublishedForProgram(?string $locale = null): array
    {
        $locale ??= SiteContext::locale();
        if ($locale !== 'fr' && $locale !== 'en') {
            $locale = 'fr';
        }

        return $this->where('publication_state', self::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->orderBy('list_section', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('published_at', 'DESC')
            ->findAll(100);
    }

    /**
     * Autres déclarations publiées (même locale et section de liste).
     *
     * @return list<array{slug: string, title: string}>
     */
    public function listRelatedPublished(int $excludeId, string $locale, string $listSection, int $limit = 4): array
    {
        if ($locale !== 'fr' && $locale !== 'en') {
            $locale = 'fr';
        }
        $limit = max(1, min(8, $limit));

        $rows = $this->where('publication_state', self::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->where('id !=', $excludeId)
            ->where('list_section', $listSection)
            ->orderBy('published_at', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll($limit);

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $out[] = [
                'slug'  => $slug,
                'title' => (string) ($row['title'] ?? $slug),
            ];
        }

        return $out;
    }
}
