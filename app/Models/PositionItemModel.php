<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\SiteContext;
use CodeIgniter\Model;

class PositionItemModel extends Model
{
    public const TYPE_DENIAL    = 'denial';
    public const TYPE_PRAISE    = 'praise';
    public const TYPE_ANALYSIS  = 'analysis';
    public const TYPE_SOLUTION  = 'solution';

    public const PUBLICATION_DRAFT     = 'draft';
    public const PUBLICATION_PUBLISHED = 'published';

    protected $table            = 'position_items';
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
        'excerpt',
        'summary',
        'body',
        'body_content_mode',
        'body_blocks',
        'types_csv',
        'sectors_csv',
        'reading_minutes',
        'publication_state',
        'meta_title',
        'meta_description',
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
    public static function typeCodes(): array
    {
        return [
            self::TYPE_DENIAL,
            self::TYPE_PRAISE,
            self::TYPE_ANALYSIS,
            self::TYPE_SOLUTION,
        ];
    }

    /** @return array<string, string> */
    public static function typeLabelsFr(): array
    {
        return [
            self::TYPE_DENIAL   => 'Alerte',
            self::TYPE_PRAISE   => 'Félicitation',
            self::TYPE_ANALYSIS => 'Analyse',
            self::TYPE_SOLUTION => 'Solution',
        ];
    }

    /** @return array<string, string> */
    public static function publicationStateLabels(): array
    {
        return [
            self::PUBLICATION_DRAFT     => 'Brouillon',
            self::PUBLICATION_PUBLISHED => 'Publié',
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
    public function listPublishedRecent(int $limit = 48, ?string $locale = null): array
    {
        $locale ??= SiteContext::locale();
        if ($locale !== 'fr' && $locale !== 'en') {
            $locale = 'fr';
        }

        $limit = max(1, min(100, $limit));

        return $this->where('publication_state', self::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->orderBy('published_at', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll($limit);
    }

    /**
     * Autres positions publiées (même locale, secteurs en commun si possible).
     *
     * @return list<array{slug: string, title: string}>
     */
    public function listRelatedPublished(int $excludeId, string $locale, string $sectorsCsv, int $limit = 4): array
    {
        if ($locale !== 'fr' && $locale !== 'en') {
            $locale = 'fr';
        }
        $limit = max(1, min(8, $limit));

        $wanted = [];
        foreach (array_filter(array_map('trim', explode(',', strtolower($sectorsCsv)))) as $code) {
            if ($code !== '') {
                $wanted[$code] = true;
            }
        }

        $rows = $this->where('publication_state', self::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->where('id !=', $excludeId)
            ->orderBy('published_at', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll(24);

        $scored = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $score = 0;
            foreach (array_filter(array_map('trim', explode(',', strtolower((string) ($row['sectors_csv'] ?? ''))))) as $code) {
                if (isset($wanted[$code])) {
                    $score++;
                }
            }
            $scored[] = ['score' => $score, 'slug' => $slug, 'title' => (string) ($row['title'] ?? $slug)];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $out = [];
        foreach ($scored as $item) {
            $out[] = ['slug' => $item['slug'], 'title' => $item['title']];
            if (count($out) >= $limit) {
                break;
            }
        }

        return $out;
    }
}
