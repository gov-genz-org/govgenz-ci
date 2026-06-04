<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\SiteContext;
use CodeIgniter\Model;

class StructureUnitModel extends Model
{
    protected $table            = 'structure_units';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'unit_role',
        'title_fr',
        'title_en',
        'subtitle_fr',
        'subtitle_en',
        'description_fr',
        'description_en',
        'contact_email',
        'media_id',
        'media_alt',
        'is_active',
        'sort_order',
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
    public function listOrderedActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listFunctionsActive(): array
    {
        $out = [];
        foreach ($this->listOrderedActive() as $row) {
            if (strtolower(trim((string) ($row['unit_role'] ?? 'function'))) === 'core') {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findActiveCore(): ?array
    {
        foreach ($this->listOrderedActive() as $row) {
            if (strtolower(trim((string) ($row['unit_role'] ?? ''))) === 'core') {
                return $row;
            }
        }

        return null;
    }

    public function titleForRow(array $row): string
    {
        $loc = SiteContext::locale();

        if ($loc === 'en') {
            return (string) ($row['title_en'] ?? $row['title_fr'] ?? $row['code'] ?? '');
        }

        return (string) ($row['title_fr'] ?? $row['title_en'] ?? $row['code'] ?? '');
    }

    public function descriptionForRow(array $row): string
    {
        $loc = SiteContext::locale();

        if ($loc === 'en') {
            return (string) ($row['description_en'] ?? $row['description_fr'] ?? '');
        }

        return (string) ($row['description_fr'] ?? $row['description_en'] ?? '');
    }

    public function subtitleForRow(array $row): string
    {
        $loc = SiteContext::locale();

        if ($loc === 'en') {
            $sub = trim((string) ($row['subtitle_en'] ?? ''));
            if ($sub !== '') {
                return $sub;
            }

            return trim((string) ($row['subtitle_fr'] ?? ''));
        }

        $sub = trim((string) ($row['subtitle_fr'] ?? ''));
        if ($sub !== '') {
            return $sub;
        }

        return trim((string) ($row['subtitle_en'] ?? ''));
    }

    public function iconUrlForRow(array $row): ?string
    {
        $mediaId = (int) ($row['media_id'] ?? 0);
        if ($mediaId > 0 && function_exists('cms_media_public_url')) {
            $url = cms_media_public_url($mediaId);
            if ($url !== null) {
                return $url;
            }
        }

        return self::defaultIconUrlForCode((string) ($row['code'] ?? ''));
    }

    public static function defaultIconUrlForCode(string $code): ?string
    {
        $code = strtolower(trim($code));
        if ($code === '' || ! preg_match('/^[a-z][a-z0-9_-]{0,30}$/', $code)) {
            return null;
        }

        $relative = 'assets/icons/structures/' . $code . '.svg';
        if (! is_file(FCPATH . $relative)) {
            return null;
        }

        return base_url($relative);
    }

    public function iconAltForRow(array $row): string
    {
        $alt = trim((string) ($row['media_alt'] ?? ''));
        if ($alt !== '') {
            return $alt;
        }

        return $this->titleForRow($row);
    }
}
