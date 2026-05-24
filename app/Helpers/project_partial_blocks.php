<?php

declare(strict_types=1);

if (! function_exists('project_has_financial_funding')) {
    /**
     * Budget carte ou bloc budget_table avec lignes.
     *
     * @param array<string, mixed> $project
     */
    function project_has_financial_funding(array $project): bool
    {
        if (trim((string) ($project['budget_display'] ?? '')) !== '') {
            return true;
        }
        foreach (project_body_blocks_list($project) as $block) {
            if (! is_array($block) || (string) ($block['type'] ?? '') !== 'budget_table') {
                continue;
            }
            $rows = $block['rows'] ?? [];
            if (is_array($rows) && $rows !== []) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('project_has_material_needs')) {
    /**
     * @param array<string, mixed> $project
     */
    function project_has_material_needs(array $project): bool
    {
        foreach (project_body_blocks_list($project) as $block) {
            if (! is_array($block) || (string) ($block['type'] ?? '') !== 'material_needs') {
                continue;
            }
            $rows = $block['rows'] ?? [];
            if (is_array($rows) && $rows !== []) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('project_geography_front_display')) {
    /**
     * Géographie structurée pour le front : niveau le plus fin, séparateur « & », max 3 + survol.
     *
     * @param array<string, mixed> $project
     *
     * @return array{text: string, html: string}
     */
    function project_geography_front_display(array $project): array
    {
        return \App\Libraries\ProjectGeographyPayload::frontDisplayFromProject($project);
    }
}

if (! function_exists('project_body_blocks_list')) {
    /**
     * @param array<string, mixed> $project
     *
     * @return list<array<string, mixed>>
     */
    function project_body_blocks_list(array $project): array
    {
        if (project_body_content_mode($project) !== 'blocks') {
            return [];
        }
        $raw = $project['body_blocks'] ?? null;
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}

