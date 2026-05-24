<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\SiteContext;
use App\Models\MdgCommuneModel;
use App\Models\MdgDistrictModel;
use App\Models\MdgFokontanyModel;
use App\Models\MdgRegionModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Sélection géographique projet (national / multi-régions…).
 */
final class ProjectGeographyPayload
{
    /**
     * @return array{geography: ?string, geography_data: ?string}
     */
    public static function fromRequest(IncomingRequest $request): array
    {
        $national = (string) $request->getPost('geo_national') === '1';
        if ($national) {
            $data = ['national' => true];

            return [
                'geography'      => 'National',
                'geography_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ];
        }

        $regionIds    = self::intIds($request->getPost('geo_region_ids'));
        $districtIds  = self::intIds($request->getPost('geo_district_ids'));
        $communeIds   = self::intIds($request->getPost('geo_commune_ids'));
        $fokontanyIds = self::intIds($request->getPost('geo_fokontany_ids'));

        if ($regionIds === [] && $districtIds === [] && $communeIds === [] && $fokontanyIds === []) {
            return [
                'geography'      => null,
                'geography_data' => null,
            ];
        }

        $data = [
            'national'      => false,
            'region_ids'    => $regionIds,
            'district_ids'  => $districtIds,
            'commune_ids'   => $communeIds,
            'fokontany_ids' => $fokontanyIds,
        ];

        $locale = self::localeFromRequest($request);

        return [
            'geography'      => self::buildDisplayLabel($data, $locale),
            'geography_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * @return array{
     *   national: bool,
     *   region_ids: list<int>,
     *   district_ids: list<int>,
     *   commune_ids: list<int>,
     *   fokontany_ids: list<int>,
     *   legacy: string
     * }
     */
    /**
     * État pour le formulaire admin (projet + éventuel old() après erreur).
     *
     * @return array{
     *   national: bool,
     *   region_ids: list<int>,
     *   district_ids: list<int>,
     *   commune_ids: list<int>,
     *   fokontany_ids: list<int>,
     *   legacy: string
     * }
     */
    public static function formStateForView(?array $project): array
    {
        $state = self::formState($project);

        if (old('geo_national') !== null) {
            $national = (string) old('geo_national') === '1';
            if ($national) {
                return [
                    'national'      => true,
                    'region_ids'    => [],
                    'district_ids'  => [],
                    'commune_ids'   => [],
                    'fokontany_ids' => [],
                    'legacy'        => '',
                ];
            }

            return [
                'national'      => false,
                'region_ids'    => self::intIds(old('geo_region_ids')),
                'district_ids'  => self::intIds(old('geo_district_ids')),
                'commune_ids'   => self::intIds(old('geo_commune_ids')),
                'fokontany_ids' => self::intIds(old('geo_fokontany_ids')),
                'legacy'        => '',
            ];
        }

        return $state;
    }

    public static function formState(?array $project): array
    {
        $empty = [
            'national'      => false,
            'region_ids'    => [],
            'district_ids'  => [],
            'commune_ids'   => [],
            'fokontany_ids' => [],
            'legacy'        => '',
        ];

        if ($project === null) {
            return $empty;
        }

        $raw = trim((string) ($project['geography_data'] ?? ''));
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                if (! empty($decoded['national'])) {
                    return [
                        'national'      => true,
                        'region_ids'    => [],
                        'district_ids'  => [],
                        'commune_ids'   => [],
                        'fokontany_ids' => [],
                        'legacy'        => '',
                    ];
                }

                return [
                    'national'      => false,
                    'region_ids'    => self::intIds($decoded['region_ids'] ?? []),
                    'district_ids'  => self::intIds($decoded['district_ids'] ?? []),
                    'commune_ids'   => self::intIds($decoded['commune_ids'] ?? []),
                    'fokontany_ids' => self::intIds($decoded['fokontany_ids'] ?? []),
                    'legacy'        => '',
                ];
            }
        }

        $legacy = trim((string) ($project['geography'] ?? ''));
        if ($legacy !== '' && preg_match('/^national$/iu', $legacy) === 1) {
            return [
                'national'      => true,
                'region_ids'    => [],
                'district_ids'  => [],
                'commune_ids'   => [],
                'fokontany_ids' => [],
                'legacy'        => '',
            ];
        }

        $empty['legacy'] = $legacy;

        return $empty;
    }

    /**
     * @param array{
     *   national: bool,
     *   region_ids: list<int>,
     *   district_ids: list<int>,
     *   commune_ids: list<int>,
     *   fokontany_ids: list<int>
     * } $data
     */
    public static function buildDisplayLabel(array $data, ?string $locale = null): string
    {
        if (! empty($data['national'])) {
            return 'National';
        }

        return self::previewFromFormState([
            'national'      => ! empty($data['national']),
            'region_ids'    => $data['region_ids'] ?? [],
            'district_ids'  => $data['district_ids'] ?? [],
            'commune_ids'   => $data['commune_ids'] ?? [],
            'fokontany_ids' => $data['fokontany_ids'] ?? [],
            'legacy'        => '',
        ], $locale)['text'];
    }

    /**
     * Aperçu admin / front : ≤2 noms avec « & » ; 3+ → « {n} régions » (survol = liste complète).
     *
     * @param array{
     *   national: bool,
     *   region_ids: list<int>,
     *   district_ids: list<int>,
     *   commune_ids: list<int>,
     *   fokontany_ids: list<int>,
     *   legacy?: string
     * } $state
     *
     * @return array{text: string, html: string}
     */
    public static function previewFromFormState(array $state, ?string $locale = null): array
    {
        $locale = self::normalizeLocale($locale);

        if ($state['national']) {
            return self::formatFrontNames(['National'], null, $locale);
        }

        $payload = self::finestLevelPayload([
            'national'      => false,
            'region_ids'    => $state['region_ids'],
            'district_ids'  => $state['district_ids'],
            'commune_ids'   => $state['commune_ids'],
            'fokontany_ids' => $state['fokontany_ids'],
        ]);
        if ($payload['names'] !== []) {
            return self::formatFrontNames($payload['names'], $payload['level'], $locale);
        }

        $legacy = trim((string) ($state['legacy'] ?? ''));
        if ($legacy === '') {
            return ['text' => '', 'html' => ''];
        }

        return [
            'text' => $legacy,
            'html' => htmlspecialchars($legacy, ENT_QUOTES, 'UTF-8'),
        ];
    }

    /**
     * Libellés au niveau géographique le plus fin (régions, districts, etc.).
     *
     * @param array{
     *   national?: bool,
     *   region_ids?: list<int>,
     *   district_ids?: list<int>,
     *   commune_ids?: list<int>,
     *   fokontany_ids?: list<int>
     * } $data
     *
     * @return list<string>
     */
    public static function namesAtFinestLevel(array $data): array
    {
        return self::finestLevelPayload($data)['names'];
    }

    /**
     * @param array{
     *   national?: bool,
     *   region_ids?: list<int>,
     *   district_ids?: list<int>,
     *   commune_ids?: list<int>,
     *   fokontany_ids?: list<int>
     * } $data
     *
     * @return array{level: ?string, names: list<string>}
     */
    public static function finestLevelPayload(array $data): array
    {
        if (! empty($data['national'])) {
            return ['level' => null, 'names' => ['National']];
        }

        if (($data['fokontany_ids'] ?? []) !== []) {
            return [
                'level' => 'fokontany',
                'names' => self::rowNames(model(MdgFokontanyModel::class)->labelsByIds($data['fokontany_ids'])),
            ];
        }
        if (($data['commune_ids'] ?? []) !== []) {
            return [
                'level' => 'communes',
                'names' => self::rowNames(model(MdgCommuneModel::class)->labelsByIds($data['commune_ids'])),
            ];
        }
        if (($data['district_ids'] ?? []) !== []) {
            return [
                'level' => 'districts',
                'names' => self::rowNames(model(MdgDistrictModel::class)->labelsByIds($data['district_ids'])),
            ];
        }
        if (($data['region_ids'] ?? []) !== []) {
            return [
                'level' => 'regions',
                'names' => self::rowNames(model(MdgRegionModel::class)->labelsByIds($data['region_ids'])),
            ];
        }

        return ['level' => null, 'names' => []];
    }

    /**
     * Modèles {0} pour l’aperçu JS admin.
     *
     * @return array{regions: string, districts: string, communes: string, fokontany: string}
     */
    public static function countLabelTemplates(?string $locale = null): array
    {
        $locale = self::normalizeLocale($locale);
        $lang   = \Config\Services::language();

        return [
            'regions'   => $lang->getLine('Projects.geo_count_regions', [], $locale),
            'districts' => $lang->getLine('Projects.geo_count_districts', [], $locale),
            'communes'  => $lang->getLine('Projects.geo_count_communes', [], $locale),
            'fokontany' => $lang->getLine('Projects.geo_count_fokontany', [], $locale),
        ];
    }

    /**
     * Texte + HTML pour le front (≤2 libellés visibles ; 3+ → 3 + détail au survol).
     *
     * @param array<string, mixed>|null $project
     *
     * @return array{text: string, html: string}
     */
    public static function frontDisplayFromProject(?array $project): array
    {
        $empty = ['text' => '', 'html' => ''];
        if ($project === null) {
            return $empty;
        }

        $locale = self::normalizeLocale(isset($project['locale']) ? (string) $project['locale'] : null);

        $raw = trim((string) ($project['geography_data'] ?? ''));
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                if (! empty($decoded['national'])) {
                    return self::formatFrontNames(['National'], null, $locale);
                }

                $payload = self::finestLevelPayload([
                    'national'      => false,
                    'region_ids'    => self::intIds($decoded['region_ids'] ?? []),
                    'district_ids'  => self::intIds($decoded['district_ids'] ?? []),
                    'commune_ids'   => self::intIds($decoded['commune_ids'] ?? []),
                    'fokontany_ids' => self::intIds($decoded['fokontany_ids'] ?? []),
                ]);
                if ($payload['names'] !== []) {
                    return self::formatFrontNames($payload['names'], $payload['level'], $locale);
                }
            }
        }

        $legacy = trim((string) ($project['geography'] ?? ''));
        if ($legacy === '') {
            return $empty;
        }
        if (preg_match('/^national$/iu', $legacy) === 1) {
            return self::formatFrontNames(['National'], null, $locale);
        }

        return [
            'text' => $legacy,
            'html' => htmlspecialchars($legacy, ENT_QUOTES, 'UTF-8'),
        ];
    }

    /**
     * @param list<string>  $names
     * @param ?string       $level regions|districts|communes|fokontany
     *
     * @return array{text: string, html: string}
     */
    public static function formatFrontNames(array $names, ?string $level = null, ?string $locale = null): array
    {
        $locale = self::normalizeLocale($locale);
        $names  = array_values(array_filter(
            array_map(static fn (mixed $n): string => trim((string) $n), $names),
            static fn (string $n): bool => $n !== ''
        ));
        if ($names === []) {
            return ['text' => '', 'html' => ''];
        }

        if (count($names) === 1 && strcasecmp($names[0], 'National') === 0) {
            $esc = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

            return ['text' => 'National', 'html' => $esc('National')];
        }

        $full = implode(self::frontSeparator(), $names);
        $esc  = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        if (count($names) <= 2) {
            return [
                'text' => $full,
                'html' => self::namesToHtml($names),
            ];
        }

        $summary = self::countLabel($level, count($names), $locale);

        return [
            'text' => $summary,
            'html' => self::geoSummaryWithInfoIcon($summary, $full, $locale),
        ];
    }

    private static function geoSummaryWithInfoIcon(string $summary, string $full, string $locale): string
    {
        helper('language');
        $esc  = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $hint = lang('Projects.geo_info_aria', [], $locale) . ' — ' . $full;

        return '<span class="project-geo project-geo--has-tip">'
            . '<span class="project-geo__summary">' . $esc($summary) . '</span>'
            . '<span class="project-geo__info" tabindex="0" role="button" aria-label="' . $esc($hint) . '">i</span>'
            . '<span class="project-geo__tip" role="tooltip">' . $esc($full) . '</span>'
            . '</span>';
    }

    private static function countLabel(?string $level, int $count, string $locale): string
    {
        helper('language');

        $key = match ($level) {
            'fokontany' => 'Projects.geo_count_fokontany',
            'communes'  => 'Projects.geo_count_communes',
            'districts' => 'Projects.geo_count_districts',
            'regions'   => 'Projects.geo_count_regions',
            default     => 'Projects.geo_count_regions',
        };

        return lang($key, [(string) $count], $locale);
    }

    private static function frontSeparator(): string
    {
        return ' & ';
    }

    /**
     * @param list<array{name?: string, label?: string}> $rows
     *
     * @return list<string>
     */
    private static function rowNames(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $name = trim((string) ($row['label'] ?? ''));
            }
            if ($name !== '') {
                $out[] = $name;
            }
        }

        return $out;
    }

    /**
     * @param list<string> $names
     */
    private static function namesToHtml(array $names): string
    {
        if ($names === []) {
            return '';
        }

        $esc = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $sep = self::frontSeparator();
        $buf = $esc($names[0]);
        for ($i = 1, $c = count($names); $i < $c; $i++) {
            $buf .= '<span class="project-geo__sep">' . $esc($sep) . '</span>' . $esc($names[$i]);
        }

        return $buf;
    }

    private static function localeFromRequest(IncomingRequest $request): string
    {
        return self::normalizeLocale((string) $request->getPost('locale'));
    }

    private static function normalizeLocale(?string $locale): string
    {
        if ($locale === null || $locale === '') {
            return SiteContext::locale() === 'en' ? 'en' : 'fr';
        }

        return strtolower(trim($locale)) === 'en' ? 'en' : 'fr';
    }

    /**
     * @return list<int>
     */
    private static function intIds(mixed $raw): array
    {
        if (! is_array($raw)) {
            if ($raw === null || $raw === '') {
                return [];
            }
            $raw = [$raw];
        }

        $out = [];
        foreach ($raw as $v) {
            if (! is_numeric($v)) {
                continue;
            }
            $id = (int) $v;
            if ($id > 0) {
                $out[$id] = true;
            }
        }

        return array_map('intval', array_keys($out));
    }
}
