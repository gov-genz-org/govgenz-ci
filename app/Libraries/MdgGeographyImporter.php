<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Importe docs/regions-communes-district.sql + docs/mada_fokontany_payload.csv
 */
final class MdgGeographyImporter
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function import(string $sqlPath, ?string $fokontanyCsvPath = null): void
    {
        if (! is_readable($sqlPath)) {
            throw new \RuntimeException('Fichier SQL introuvable : ' . $sqlPath);
        }

        $sql = file_get_contents($sqlPath);
        if ($sql === false) {
            throw new \RuntimeException('Lecture SQL impossible.');
        }

        $this->db->disableForeignKeyChecks();
        $this->db->table('mdg_fokontany')->truncate();
        $this->db->table('mdg_communes')->truncate();
        $this->db->table('mdg_districts')->truncate();
        $this->db->table('mdg_regions')->truncate();
        $this->db->table('mdg_provinces')->truncate();

        foreach ($this->parseProvinceRows($sql) as $row) {
            $this->db->table('mdg_provinces')->insert($row);
        }
        foreach ($this->parseTwoIntNameRows($sql, 'region', 'id_province', 'nom') as $row) {
            $this->db->table('mdg_regions')->insert([
                'id'          => $row['id'],
                'province_id' => $row['parent_id'],
                'name'        => $row['name'],
            ]);
        }
        foreach ($this->parseTwoIntNameRows($sql, 'district', 'id_region', 'libelle') as $row) {
            $this->db->table('mdg_districts')->insert([
                'id'        => $row['id'],
                'region_id' => $row['parent_id'],
                'name'      => $row['name'],
            ]);
        }
        foreach ($this->parseTwoIntNameRows($sql, 'commune', 'id_district', 'nom') as $row) {
            $this->db->table('mdg_communes')->insert([
                'id'          => $row['id'],
                'district_id' => $row['parent_id'],
                'name'        => $row['name'],
            ]);
        }

        if ($fokontanyCsvPath !== null && is_readable($fokontanyCsvPath)) {
            $this->importFokontanyCsv($fokontanyCsvPath);
        }

        $this->db->enableForeignKeyChecks();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function parseProvinceRows(string $sql): array
    {
        $block = $this->extractInsertBlock($sql, 'province');
        if ($block === null) {
            return [];
        }

        $out = [];
        if (preg_match_all("/\((\d+),\s*'((?:[^'\\\\]|\\\\.|'')*)'\)/u", $block, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $out[] = [
                    'id'   => (int) $row[1],
                    'name' => $this->unescapeSqlString($row[2]),
                ];
            }
        }

        return $out;
    }

    /**
     * @return list<array{id: int, parent_id: int, name: string}>
     */
    private function parseTwoIntNameRows(string $sql, string $table, string $parentCol, string $nameCol): array
    {
        $block = $this->extractInsertBlock($sql, $table);
        if ($block === null) {
            return [];
        }

        $out = [];
        if (preg_match_all("/\((\d+),\s*(\d+),\s*'((?:[^'\\\\]|\\\\.|'')*)'\)/u", $block, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $out[] = [
                    'id'        => (int) $row[1],
                    'parent_id' => (int) $row[2],
                    'name'      => $this->unescapeSqlString($row[3]),
                ];
            }
        }

        return $out;
    }

    private function extractInsertBlock(string $sql, string $table): ?string
    {
        if (preg_match('/INSERT INTO `' . preg_quote($table, '/') . '`[^;]+;/s', $sql, $m)) {
            return $m[0];
        }

        return null;
    }

    private function unescapeSqlString(string $value): string
    {
        return str_replace(["\\'", '\\\\'], ["'", '\\'], $value);
    }

    private function importFokontanyCsv(string $csvPath): void
    {
        $regions = [];
        foreach ($this->db->table('mdg_regions')->get()->getResultArray() as $r) {
            $regions[$this->normKey((string) $r['name'])] = (int) $r['id'];
        }

        $districts = [];
        foreach ($this->db->table('mdg_districts')->get()->getResultArray() as $d) {
            $districts[(int) $d['region_id'] . '|' . $this->normKey((string) $d['name'])] = (int) $d['id'];
        }

        $communes = [];
        foreach ($this->db->table('mdg_communes')->get()->getResultArray() as $c) {
            $communes[(int) $c['district_id'] . '|' . $this->normKey((string) $c['name'])] = (int) $c['id'];
        }

        $fh = fopen($csvPath, 'rb');
        if ($fh === false) {
            throw new \RuntimeException('Ouverture CSV impossible.');
        }

        $header = fgetcsv($fh, 0, "\t");
        $batch = [];
        $batchSize = 400;
        $skipped = 0;

        while (($line = fgetcsv($fh, 0, "\t")) !== false) {
            if (count($line) < 4) {
                continue;
            }
            $regionName   = trim((string) $line[0]);
            $districtName = trim((string) $line[1]);
            $communeName  = trim((string) $line[2]);
            $fokName      = trim((string) $line[3]);
            if ($fokName === '') {
                continue;
            }

            $regionId = $regions[$this->normKey($regionName)] ?? null;
            if ($regionId === null) {
                $skipped++;
                continue;
            }
            $districtId = $districts[$regionId . '|' . $this->normKey($districtName)] ?? null;
            if ($districtId === null) {
                $skipped++;
                continue;
            }
            $communeId = $communes[$districtId . '|' . $this->normKey($communeName)] ?? null;
            if ($communeId === null) {
                $skipped++;
                continue;
            }

            $batch[] = [
                'commune_id' => $communeId,
                'name'       => $fokName,
            ];
            if (count($batch) >= $batchSize) {
                $this->db->table('mdg_fokontany')->insertBatch($batch);
                $batch = [];
            }
        }

        fclose($fh);

        if ($batch !== []) {
            $this->db->table('mdg_fokontany')->insertBatch($batch);
        }

        if ($skipped > 0) {
            log_message('warning', 'Import fokontany : {count} lignes ignorées (nom non trouvé).', ['count' => $skipped]);
        }
    }

    private function normKey(string $value): string
    {
        return mb_strtoupper(trim($value));
    }
}
