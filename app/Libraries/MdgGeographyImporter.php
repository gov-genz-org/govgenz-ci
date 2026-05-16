<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Si liste_fokontany_par_district.json est présent : régions, districts, communes et fokontany
 * sont dérivés du JSON (référence à jour). Le SQL sert uniquement aux provinces et au rattachement
 * province ← région (table region historique). Sinon : référentiel géographique complet depuis le SQL
 * et fokontany depuis le CSV éventuel.
 */
final class MdgGeographyImporter
{
    /**
     * Libellés région hors carte SQL historique : aide pour province_id (import JSON) et pour l’import CSV.
     *
     * @var array<string, string>
     */
    private const REGION_NAME_ALIASES = [
        'FITOVINANY' => 'Vatovavy-Fitovinany',
        'VATOVAVY'   => 'Vatovavy-Fitovinany',
    ];

    /**
     * Pour l’import CSV uniquement : rapprochements avec l’ancien référentiel SQL.
     *
     * @var array<string, string>
     */
    private const DISTRICT_NAME_ALIASES = [
        'AMBATO BOENI'        => 'Ambato-Boeny',
        'AMBOVOMBE ANDROY'    => 'Ambovombe',
        'AMPANIHY OUEST'      => 'Ampanihy',
        'ANKAZOABO SUD'       => 'Ankazoabo',
        'ANOSIBE AN ALA'      => 'Anosibe-An\'ala',
        'ANTANAMBAO MANAMPO'  => 'Antanambao-Manampotsy',
        'BEFANDRIANA NORD'    => 'Befandriana-Avaratra',
        'BEFOTAKA ATSIMO'     => 'Befotaka',
        'BELOHA ANDROY'       => 'Beloha',
        'FENERIVE EST'        => 'Fénérive-Est',
        'MANANARA-NORD'       => 'Manarana-Nord',
        'MIDONGY SUD'         => 'Midongy',
        'PORT-BERGE'          => 'Boriziny',
        'SAINTE MARIE'        => 'Sainte-Marie/NosyBoraha',
        'TOAMASINA I'        => 'Tamatave-I',
        'TOAMASINA II'       => 'Tamatave-II',
        'TOLIARY I'          => 'Tuléar-I',
        'TOLIARY II'         => 'Tuléar-II',
        'TSIHOMBE'           => 'Tsiombe',
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    public function import(string $sqlPath, ?string $fokontanyCsvPath = null, ?string $fokontanyJsonPath = null): void
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

        if ($fokontanyJsonPath !== null && is_readable($fokontanyJsonPath)) {
            $this->importFromAuthoritativeJson($fokontanyJsonPath, $sql);
        } else {
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
        }

        GeoCatalogCache::flushAll();

        $this->db->enableForeignKeyChecks();
    }

    private function importFromAuthoritativeJson(string $jsonPath, string $sql): void
    {
        $raw = file_get_contents($jsonPath);
        if ($raw === false) {
            throw new \RuntimeException('Lecture JSON géographie impossible.');
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            throw new \RuntimeException('JSON géographie invalide ou trop volumineux (json_decode).');
        }

        $model            = $this->collectAuthoritativeJsonModel($data);
        $provinceLookup = $this->buildSqlRegionProvinceLookup($sql);

        $regionNormToId = [];
        $nextRegionId   = 1;

        $regionNorms = array_keys($model['regions']);
        usort($regionNorms, static function (string $a, string $b) use ($model): int {
            return mb_strtolower($model['regions'][$a]['label']) <=> mb_strtolower($model['regions'][$b]['label']);
        });

        foreach ($regionNorms as $rn) {
            $label      = $model['regions'][$rn]['label'];
            $provinceId = $this->inferProvinceIdForJsonRegion($label, $provinceLookup);
            $this->db->table('mdg_regions')->insert([
                'id'          => $nextRegionId,
                'province_id' => $provinceId,
                'name'        => $label,
            ]);
            $regionNormToId[$rn] = $nextRegionId;
            $nextRegionId++;
        }

        $districtPairToId = [];
        $nextDistrictId   = 1;

        $districtKeys = array_keys($model['districts']);
        usort($districtKeys, static function (string $ka, string $kb) use ($model, $regionNormToId): int {
            [$rna] = explode('|', $ka, 2);
            [$rnb] = explode('|', $kb, 2);
            $cmp = ($regionNormToId[$rna] ?? 0) <=> ($regionNormToId[$rnb] ?? 0);
            if ($cmp !== 0) {
                return $cmp;
            }

            return mb_strtolower($model['districts'][$ka]['label'])
                <=> mb_strtolower($model['districts'][$kb]['label']);
        });

        foreach ($districtKeys as $dk) {
            [$rn] = explode('|', $dk, 2);
            $regionId = $regionNormToId[$rn] ?? null;
            if ($regionId === null) {
                continue;
            }
            $label = $model['districts'][$dk]['label'];
            $this->db->table('mdg_districts')->insert([
                'id'        => $nextDistrictId,
                'region_id' => $regionId,
                'name'      => $label,
            ]);
            $districtPairToId[$dk] = $nextDistrictId;
            $nextDistrictId++;
        }

        $tripleToCommuneId = [];
        $nextCommuneId     = 1;

        $communeKeys = array_keys($model['communes']);
        usort($communeKeys, static function (string $ka, string $kb) use ($model, $districtPairToId): int {
            [$rna, $resta] = explode('|', $ka, 2);
            [$dna]         = explode('|', $resta, 2);
            [$rnb, $restb] = explode('|', $kb, 2);
            [$dnb]         = explode('|', $restb, 2);
            $dida           = $districtPairToId[$rna . '|' . $dna] ?? 0;
            $didb           = $districtPairToId[$rnb . '|' . $dnb] ?? 0;
            $cmp            = $dida <=> $didb;
            if ($cmp !== 0) {
                return $cmp;
            }

            return mb_strtolower($model['communes'][$ka]['label'])
                <=> mb_strtolower($model['communes'][$kb]['label']);
        });

        foreach ($communeKeys as $ck) {
            [$rn, $rest] = explode('|', $ck, 2);
            [$dn]        = explode('|', $rest, 2);
            $districtKey = $rn . '|' . $dn;
            $districtId  = $districtPairToId[$districtKey] ?? null;
            if ($districtId === null) {
                continue;
            }
            $label = $model['communes'][$ck]['label'];
            $this->db->table('mdg_communes')->insert([
                'id'          => $nextCommuneId,
                'district_id' => $districtId,
                'name'        => $label,
            ]);
            $tripleToCommuneId[$ck] = $nextCommuneId;
            $nextCommuneId++;
        }

        $batch     = [];
        $batchSize = 400;
        $skipped   = 0;

        foreach ($model['fokontany'] as $row) {
            $ck = $row['rn'] . '|' . $row['dn'] . '|' . $row['cn'];
            $cid = $tripleToCommuneId[$ck] ?? null;
            if ($cid === null) {
                $skipped++;
                continue;
            }
            $batch[] = [
                'commune_id' => $cid,
                'name'       => $row['name'],
            ];
            if (count($batch) >= $batchSize) {
                $this->db->table('mdg_fokontany')->insertBatch($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->db->table('mdg_fokontany')->insertBatch($batch);
        }

        if ($skipped > 0) {
            log_message('warning', 'Import JSON : {count} fokontany sans commune résolue (cohérence).', ['count' => $skipped]);
        }

        log_message(
            'info',
            'Géographie JSON : {r} régions, {d} districts, {c} communes, {f} lignes fokontany.',
            [
                'r' => count($model['regions']),
                'd' => count($model['districts']),
                'c' => count($model['communes']),
                'f' => count($model['fokontany']),
            ]
        );
    }

    /**
     * @return array{
     *   regions: array<string, array{label: string}>,
     *   districts: array<string, array{region_norm: string, label: string}>,
     *   communes: array<string, array{region_norm: string, district_norm: string, label: string}>,
     *   fokontany: list<array{rn: string, dn: string, cn: string, name: string}>
     * }
     */
    private function collectAuthoritativeJsonModel(array $data): array
    {
        $regions   = [];
        $districts = [];
        $communes  = [];
        $fokontany = [];

        foreach ($data as $regionKey => $byDistrict) {
            if ($regionKey === 'Region' || $regionKey === 'TOTAL' || ! is_array($byDistrict)) {
                continue;
            }

            foreach ($byDistrict as $districtKey => $byCommune) {
                if ($districtKey === 'District' || ! is_array($byCommune)) {
                    continue;
                }
                if ($this->isJunkAdministrativeLabel((string) $districtKey)) {
                    continue;
                }

                foreach ($byCommune as $communeKey => $entries) {
                    if ($communeKey === 'Commune' || ! is_array($entries)) {
                        continue;
                    }

                    foreach ($entries as $entry) {
                        if (! is_array($entry)) {
                            continue;
                        }

                        $fokName = trim((string) ($entry['fokontany'] ?? ''));
                        if ($fokName === '' || strcasecmp($fokName, 'Fokontany') === 0) {
                            continue;
                        }

                        $regionName   = $this->canonicalGeoLabel(trim((string) ($entry['region'] ?? $regionKey)));
                        $districtName = $this->canonicalGeoLabel(trim((string) ($entry['district'] ?? $districtKey)));
                        $communeName  = $this->canonicalGeoLabel(trim((string) ($entry['commune'] ?? $communeKey)));

                        if ($regionName === '' || $districtName === '' || $communeName === '') {
                            continue;
                        }

                        if ($this->normKey($regionName) === 'TOTAL'
                            || $this->normKey($districtName) === 'DISTRICT'
                            || $this->normKey($communeName) === 'COMMUNE') {
                            continue;
                        }

                        $rn = $this->jsonHierarchyNormKey($regionName);
                        $dn = $this->jsonHierarchyNormKey($districtName);
                        $cn = $this->jsonHierarchyNormKey($communeName);

                        if (! isset($regions[$rn])) {
                            $regions[$rn] = ['label' => $regionName];
                        }

                        $dk = $rn . '|' . $dn;
                        if (! isset($districts[$dk])) {
                            $districts[$dk] = ['region_norm' => $rn, 'label' => $districtName];
                        }

                        $communeKeyFull = $rn . '|' . $dn . '|' . $cn;
                        if (! isset($communes[$communeKeyFull])) {
                            $communes[$communeKeyFull] = [
                                'region_norm'   => $rn,
                                'district_norm' => $dn,
                                'label'         => $communeName,
                            ];
                        }

                        $fokontany[] = [
                            'rn'   => $rn,
                            'dn'   => $dn,
                            'cn'   => $cn,
                            'name' => $fokName,
                        ];
                    }
                }
            }
        }

        return compact('regions', 'districts', 'communes', 'fokontany');
    }

    private function isJunkAdministrativeLabel(string $key): bool
    {
        $t = trim($key);

        return $t === '' || preg_match('/^[\d\s]+$/', $t) === 1;
    }

    private function jsonHierarchyNormKey(string $label): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($label)) ?? trim($label);

        return $this->normKey($collapsed);
    }

    /**
     * Ancienne table SQL `region` : aide uniquement pour province_id des lignes mdg_regions générées depuis le JSON.
     *
     * @return array<string, int>
     */
    private function buildSqlRegionProvinceLookup(string $sql): array
    {
        $map = [];
        foreach ($this->parseTwoIntNameRows($sql, 'region', 'id_province', 'nom') as $row) {
            foreach ($this->normGeoSearchKeys($row['name']) as $k) {
                if (! isset($map[$k])) {
                    $map[$k] = (int) $row['parent_id'];
                }
            }
        }

        return $map;
    }

    /**
     * @param array<string, int> $sqlRegionProvinceByNormKey
     */
    private function inferProvinceIdForJsonRegion(string $regionDisplayLabel, array $sqlRegionProvinceByNormKey): int
    {
        foreach ($this->normGeoSearchKeys($regionDisplayLabel) as $k) {
            if (isset($sqlRegionProvinceByNormKey[$k])) {
                return $sqlRegionProvinceByNormKey[$k];
            }
        }

        $aliasTarget = self::REGION_NAME_ALIASES[$this->normKey($regionDisplayLabel)] ?? null;
        if ($aliasTarget !== null) {
            foreach ($this->normGeoSearchKeys($aliasTarget) as $k) {
                if (isset($sqlRegionProvinceByNormKey[$k])) {
                    return $sqlRegionProvinceByNormKey[$k];
                }
            }
        }

        return 1;
    }

    /**
     * @return array{
     *   regions: array<string, int>,
     *   districts: array<string, int>,
     *   communes: array<string, int>,
     *   communeRegionIndex: array<string, list<array{commune_id: int, district_id: int}>>
     * }
     */
    private function loadGeoLookups(): array
    {
        $regions = [];
        foreach ($this->db->table('mdg_regions')->get()->getResultArray() as $r) {
            $rid = (int) $r['id'];
            foreach ($this->normGeoSearchKeys((string) $r['name']) as $k) {
                if (! isset($regions[$k])) {
                    $regions[$k] = $rid;
                }
            }
        }

        $districtRows = $this->db->table('mdg_districts')->get()->getResultArray();
        $districtRegion = [];
        $districts      = [];
        foreach ($districtRows as $d) {
            $did                    = (int) $d['id'];
            $rid                    = (int) $d['region_id'];
            $districtRegion[$did] = $rid;
            foreach ($this->normGeoSearchKeys((string) $d['name']) as $k) {
                $compound = $rid . '|' . $k;
                if (! isset($districts[$compound])) {
                    $districts[$compound] = $did;
                }
            }
        }

        $communes           = [];
        $communeRegionIndex = [];
        foreach ($this->db->table('mdg_communes')->get()->getResultArray() as $c) {
            $did = (int) $c['district_id'];
            $cid = (int) $c['id'];
            $rid = $districtRegion[$did] ?? null;
            $pair = ['commune_id' => $cid, 'district_id' => $did];
            foreach ($this->normGeoSearchKeys((string) $c['name']) as $k) {
                $compound = $did . '|' . $k;
                if (! isset($communes[$compound])) {
                    $communes[$compound] = $cid;
                }
                if ($rid !== null) {
                    $communeRegionIndex[$rid . '|' . $k][] = $pair;
                }
            }
        }

        return compact('regions', 'districts', 'communes', 'communeRegionIndex');
    }

    /**
     * @param array<string, mixed> $maps
     */
    private function resolveFokontanyInsert(array $maps, string $regionName, string $districtName, string $communeName, string $fokName): ?array
    {
        $regions             = $maps['regions'];
        $districts           = $maps['districts'];
        $communes            = $maps['communes'];
        $communeRegionIndex = $maps['communeRegionIndex'];

        $regionName   = $this->canonicalGeoLabel($regionName);
        $districtName = $this->applyDistrictNameAlias($districtName);
        $districtName = $this->canonicalGeoLabel($districtName);
        $communeName  = $this->canonicalGeoLabel($communeName);

        if ($this->normKey($regionName) === 'TOTAL') {
            return null;
        }

        $regionId = $this->resolveRegionId($regions, $regionName);
        if ($regionId === null) {
            return null;
        }

        $districtId = $this->resolveDistrictId($districts, $regionId, $districtName);

        $communeId = null;
        if ($districtId !== null) {
            foreach ($this->normGeoSearchKeys($communeName) as $ck) {
                $communeId = $communes[$districtId . '|' . $ck] ?? null;
                if ($communeId !== null) {
                    break;
                }
            }
        }

        if ($communeId === null) {
            $communeId = $this->resolveCommuneIdInRegion($communeRegionIndex, $regionId, $communeName, $districtId);
        }

        if ($communeId === null) {
            return null;
        }

        return [
            'commune_id' => $communeId,
            'name'       => $fokName,
        ];
    }

    /**
     * @param array<string, int> $regions
     */
    private function resolveRegionId(array $regions, string $regionName): ?int
    {
        $aliases = self::REGION_NAME_ALIASES[$this->normKey($regionName)] ?? null;
        $pool    = $aliases !== null ? [$regionName, $aliases] : [$regionName];

        foreach ($pool as $label) {
            foreach ($this->normGeoSearchKeys($label) as $rk) {
                $id = $regions[$rk] ?? null;
                if ($id !== null) {
                    return $id;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, int> $districts
     */
    private function resolveDistrictId(array $districts, int $regionId, string $districtName): ?int
    {
        foreach ($this->normGeoSearchKeys($districtName) as $dk) {
            $id = $districts[$regionId . '|' . $dk] ?? null;
            if ($id !== null) {
                return $id;
            }
        }

        return null;
    }

    /**
     * @param array<string, list<array{commune_id: int, district_id: int}>> $communeRegionIndex
     */
    private function resolveCommuneIdInRegion(
        array $communeRegionIndex,
        int $regionId,
        string $communeName,
        ?int $preferredDistrictId,
    ): ?int {
        $hits = [];
        foreach ($this->normGeoSearchKeys($communeName) as $ck) {
            foreach ($communeRegionIndex[$regionId . '|' . $ck] ?? [] as $row) {
                $hits[$row['commune_id']] = $row['district_id'];
            }
        }

        if ($hits === []) {
            return null;
        }

        if (count($hits) === 1) {
            return (int) array_key_first($hits);
        }

        if ($preferredDistrictId !== null) {
            foreach ($hits as $cid => $did) {
                if ($did === $preferredDistrictId) {
                    return $cid;
                }
            }
        }

        return null;
    }

    private function applyDistrictNameAlias(string $districtName): string
    {
        $alias = self::DISTRICT_NAME_ALIASES[$this->normKey($districtName)] ?? null;

        return $alias !== null ? $alias : $districtName;
    }

    private function canonicalGeoLabel(string $value): string
    {
        $s = trim($value);
        $s = preg_replace('/[\x{2018}\x{2019}\x{B4}]/u', "'", $s) ?? $s;

        return $s;
    }

    /**
     * Clés utilisées pour les lookups : tirets / espaces + variante sans accents (JSON souvent sans accents).
     *
     * @return list<string>
     */
    private function normGeoSearchKeys(string $value): array
    {
        $base = $this->normGeoLabelVariants($value);
        $keys = $base;
        foreach ($base as $v) {
            $fold = $this->asciiFoldUpper($v);
            if ($fold !== '') {
                $keys[] = $fold;
            }
        }

        return array_values(array_unique($keys));
    }

    private function asciiFoldUpper(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii === false || $ascii === '') {
            return '';
        }

        return strtoupper(preg_replace('/\s+/u', ' ', trim($ascii)) ?? '');
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
        $maps = $this->loadGeoLookups();

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

            $row = $this->resolveFokontanyInsert($maps, $regionName, $districtName, $communeName, $fokName);
            if ($row === null) {
                $skipped++;
                continue;
            }

            $batch[] = $row;
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
            log_message('warning', 'Import fokontany CSV : {count} lignes ignorées (nom non trouvé).', ['count' => $skipped]);
        }
    }

    private function normKey(string $value): string
    {
        return mb_strtoupper(trim($value));
    }

    /**
     * Variantes pour rapprocher référentiel SQL (ex. Antsirabe-I) et sources externes (ex. ANTSIRABE I).
     *
     * @return list<string>
     */
    private function normGeoLabelVariants(string $value): array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return [];
        }

        $collapsed = preg_replace('/\s+/u', ' ', $trimmed) ?? $trimmed;

        $candidates = [
            $collapsed,
            str_replace('-', ' ', $collapsed),
            str_replace(' ', '-', $collapsed),
        ];

        $keys = [];
        foreach ($candidates as $c) {
            $keys[] = $this->normKey($c);
        }

        return array_values(array_unique($keys));
    }
}
