<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\PositionItemModel;

/**
 * Agrégats affichés sur la liste publique des positions.
 */
final class ProgramListPositionStats
{
    /**
     * @param list<array<string, mixed>> $allPublished
     *
     * @return array{published_count: int, sectors_covered: int, types_count: int}
     */
    public static function fromPublishedRows(array $allPublished, ?PositionItemModel $model = null): array
    {
        $itemModel = $model ?? model(PositionItemModel::class);
        $locale    = SiteContext::locale();

        $publishedCount = (int) $itemModel
            ->where('publication_state', PositionItemModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->countAllResults();

        $sectorCodesSeen = [];
        foreach ($allPublished as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach (array_filter(array_map('trim', explode(',', (string) ($row['sectors_csv'] ?? '')))) as $code) {
                $c = strtolower($code);
                if ($c !== '') {
                    $sectorCodesSeen[$c] = true;
                }
            }
        }

        return [
            'published_count' => $publishedCount,
            'sectors_covered' => count($sectorCodesSeen),
            'types_count'     => count(PositionItemModel::typeCodes()),
        ];
    }
}
