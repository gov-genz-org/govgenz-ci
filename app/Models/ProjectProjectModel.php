<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\SiteContext;
use CodeIgniter\Model;

class ProjectProjectModel extends Model
{
    public const STATUS_ACTIF       = 'actif';
    public const STATUS_CANDIDAT    = 'candidat';
    public const STATUS_VALIDATION  = 'validation';
    public const STATUS_COMPLETE    = 'complete';

    public const PUBLICATION_DRAFT     = 'draft';
    public const PUBLICATION_PUBLISHED = 'published';

    public const BUDGET_SCALE_ARIARY   = 'ariary';
    public const BUDGET_SCALE_THOUSAND = 'thousand';
    public const BUDGET_SCALE_MILLION  = 'million';
    public const BUDGET_SCALE_BILLION  = 'billion';

    protected $table            = 'project_projects';
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
        'body',
        'body_content_mode',
        'body_blocks',
        'project_status',
        'publication_state',
        'sectors_csv',
        'volunteers_count',
        'budget_display',
        'budget_amount',
        'budget_scale',
        'budget_ariary',
        'geography',
        'geography_data',
        'launched_at',
        'duration_months',
        'progress_percent',
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

    /** @var array<string, string> */
    public static function projectStatusLabels(): array
    {
        return [
            self::STATUS_ACTIF      => 'Actif',
            self::STATUS_CANDIDAT   => 'Candidat',
            self::STATUS_VALIDATION => 'En validation',
            self::STATUS_COMPLETE   => 'Complété',
        ];
    }

    /** @var array<string, string> */
    public static function publicationStateLabels(): array
    {
        return [
            self::PUBLICATION_DRAFT     => 'Brouillon',
            self::PUBLICATION_PUBLISHED => 'Publié',
        ];
    }

    /** @return array<string, string> */
    public static function budgetScaleLabels(): array
    {
        return [
            self::BUDGET_SCALE_ARIARY   => 'Ariary (montant exact)',
            self::BUDGET_SCALE_THOUSAND => 'Milliers (× 1 000)',
            self::BUDGET_SCALE_MILLION  => 'Millions (× 1 000 000)',
            self::BUDGET_SCALE_BILLION  => 'Milliards (× 1 000 000 000)',
        ];
    }

    /** @return list<string> */
    public static function budgetScaleCodes(): array
    {
        return array_keys(self::budgetScaleLabels());
    }

    /**
     * Fiche publiée et non supprimée (soft delete exclu par le modèle).
     *
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
     * Liste des projets publiés (aperçu liste).
     *
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
}
