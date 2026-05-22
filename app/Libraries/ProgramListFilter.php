<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Filtres POST JSON partagés entre listes programme (projets, positions).
 */
final class ProgramListFilter
{
    /**
     * @param list<string> $allowed
     *
     * @return list<string>
     */
    public static function sanitizeList(mixed $raw, array $allowed): array
    {
        if ($allowed === []) {
            return [];
        }

        if ($raw === null || $raw === '') {
            return [];
        }

        if (! is_array($raw)) {
            $raw = [$raw];
        }

        $lookup = array_fill_keys($allowed, true);
        $seen   = [];
        foreach ($raw as $v) {
            $s = strtolower(trim((string) $v));
            if ($s === '' || ! isset($lookup[$s])) {
                continue;
            }
            $seen[$s] = true;
        }

        return array_keys($seen);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string>               $filterSectors codes minuscules ; vide = pas de filtre
     *
     * @return list<array<string, mixed>>
     */
    public static function filterBySectors(array $rows, array $filterSectors, string $sectorsCsvKey = 'sectors_csv'): array
    {
        if ($filterSectors === []) {
            return $rows;
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $csv   = strtolower((string) ($row[$sectorsCsvKey] ?? ''));
            $codes = array_filter(array_map('trim', explode(',', $csv)));
            $codes = array_map(static fn (string $c): string => strtolower($c), $codes);
            if (array_intersect($codes, $filterSectors) === []) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string>               $filterValues valeurs exactes (ex. statut projet)
     *
     * @return list<array<string, mixed>>
     */
    public static function filterByExactField(array $rows, array $filterValues, string $fieldKey): array
    {
        if ($filterValues === []) {
            return $rows;
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (! in_array((string) ($row[$fieldKey] ?? ''), $filterValues, true)) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string>               $filterTypes
     *
     * @return list<array<string, mixed>>
     */
    public static function filterByPositionTypes(array $rows, array $filterTypes): array
    {
        if ($filterTypes === []) {
            return $rows;
        }

        helper('position');
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $codes = position_types_from_csv((string) ($row['types_csv'] ?? ''));
            if (array_intersect($codes, $filterTypes) === []) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }
}
