<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\SiteContext;
use CodeIgniter\Model;

class SectorModel extends Model
{
    protected $table            = 'sectors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'label_fr',
        'label_en',
        'contact_email',
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
    public function listOrdered(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('code', 'ASC')
            ->findAll();
    }

    /**
     * @return list<string>
     */
    public function allowedCodes(): array
    {
        return array_values(array_column($this->listOrdered(), 'code'));
    }

    /**
     * Libellé selon la locale courante (FR par défaut).
     */
    public function labelForRow(array $row): string
    {
        $loc = SiteContext::locale();

        if ($loc === 'en') {
            return (string) ($row['label_en'] ?? $row['label_fr'] ?? $row['code'] ?? '');
        }

        return (string) ($row['label_fr'] ?? $row['label_en'] ?? $row['code'] ?? '');
    }

    /**
     * @return array<string, string> code => libellé (locale courante)
     */
    public function optionsForSelect(): array
    {
        $out = [];
        foreach ($this->listOrdered() as $row) {
            $code = (string) ($row['code'] ?? '');
            if ($code === '') {
                continue;
            }
            $out[$code] = $this->labelForRow($row);
        }

        return $out;
    }
}
