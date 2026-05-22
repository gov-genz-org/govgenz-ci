<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\ProjectProjectModel;

/**
 * Agrégats affichés sur la liste publique des projets (bandeau stats).
 */
final class ProgramListProjectStats
{
    /**
     * @return array{
     *   active_projects: int,
     *   volunteers_sum: int,
     *   budget_total_display: string,
     *   sectors_covered: int
     * }
     */
    public static function forLocale(string $locale, ?ProjectProjectModel $model = null): array
    {
        helper('project');

        $projectModel = $model ?? model(ProjectProjectModel::class);

        $activeCount = (int) $projectModel
            ->where('publication_state', ProjectProjectModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->where('project_status', ProjectProjectModel::STATUS_ACTIF)
            ->countAllResults();

        $volRow = $projectModel
            ->selectSum('volunteers_count', 'vsum')
            ->where('publication_state', ProjectProjectModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->first();
        $volTotal = (int) ($volRow['vsum'] ?? 0);

        $aggRows = $projectModel
            ->select('budget_ariary, budget_display, sectors_csv')
            ->where('publication_state', ProjectProjectModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->findAll();

        $budgetSumAriary = 0.0;
        $budgetParsed    = false;
        $sectorCodesSeen = [];

        foreach ($aggRows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $parsed = project_budget_ariary_for_project($row);
            if ($parsed !== null) {
                $budgetSumAriary += $parsed;
                $budgetParsed = true;
            }
            foreach (array_filter(array_map('trim', explode(',', (string) ($row['sectors_csv'] ?? '')))) as $code) {
                $c = strtolower($code);
                if ($c !== '') {
                    $sectorCodesSeen[$c] = true;
                }
            }
        }

        $budgetTotalDisplay = $budgetParsed
            ? project_format_budget_ariary_sum($budgetSumAriary, $locale)
            : lang('Projects.stats_value_emdash');

        return [
            'active_projects'      => $activeCount,
            'volunteers_sum'       => $volTotal,
            'budget_total_display' => $budgetTotalDisplay,
            'sectors_covered'      => count($sectorCodesSeen),
        ];
    }
}
