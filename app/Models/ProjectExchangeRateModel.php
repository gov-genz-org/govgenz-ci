<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProjectExchangeRateModel extends Model
{
    protected $table            = 'project_exchange_rates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'label_year',
        'usd_ariary',
        'eur_ariary',
        'cny_ariary',
        'jpy_ariary',
        'fcfa_ariary',
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
     * @return array{
     *   label_year: string,
     *   usd_ariary: float,
     *   eur_ariary: float,
     *   cny_ariary: float,
     *   jpy_ariary: float,
     *   fcfa_ariary: float
     * }
     */
    public function getConfig(): array
    {
        $row = $this->orderBy('id', 'ASC')->first();

        return self::normalizeRow($row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveConfig(array $data): void
    {
        $row = $this->orderBy('id', 'ASC')->first();
        if ($row === null) {
            $this->insert($data);

            return;
        }

        $this->update((int) $row['id'], $data);
    }

    /**
     * @return array{
     *   label_year: string,
     *   usd_ariary: float,
     *   eur_ariary: float,
     *   cny_ariary: float,
     *   jpy_ariary: float,
     *   fcfa_ariary: float
     * }
     */
    public static function defaults(): array
    {
        return [
            'label_year'  => '2026',
            'usd_ariary'  => 4500.0,
            'eur_ariary'  => 4900.0,
            'cny_ariary'  => 620.0,
            'jpy_ariary'  => 30.0,
            'fcfa_ariary' => 7.5,
        ];
    }

    /**
     * @param array<string, mixed>|null $row
     *
     * @return array{
     *   label_year: string,
     *   usd_ariary: float,
     *   eur_ariary: float,
     *   cny_ariary: float,
     *   jpy_ariary: float,
     *   fcfa_ariary: float
     * }
     */
    public static function normalizeRow(?array $row): array
    {
        $d = self::defaults();
        if ($row === null) {
            return $d;
        }

        return [
            'label_year'  => trim((string) ($row['label_year'] ?? $d['label_year'])) ?: $d['label_year'],
            'usd_ariary'  => self::positiveFloat($row['usd_ariary'] ?? null, $d['usd_ariary']),
            'eur_ariary'  => self::positiveFloat($row['eur_ariary'] ?? null, $d['eur_ariary']),
            'cny_ariary'  => self::positiveFloat($row['cny_ariary'] ?? null, $d['cny_ariary']),
            'jpy_ariary'  => self::positiveFloat($row['jpy_ariary'] ?? null, $d['jpy_ariary']),
            'fcfa_ariary' => self::positiveFloat($row['fcfa_ariary'] ?? null, $d['fcfa_ariary']),
        ];
    }

    private static function positiveFloat(mixed $value, float $fallback): float
    {
        if ($value === null || $value === '') {
            return $fallback;
        }
        $n = (float) $value;

        return $n > 0 ? $n : $fallback;
    }
}
