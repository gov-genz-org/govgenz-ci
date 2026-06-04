<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\DeclarationItemModel;
use App\Models\SectorModel;

/**
 * Bandeau chiffres de la liste publique Déclaration.
 */
final class ProgramListDeclarationStats
{
    /**
     * @param list<array<string, mixed>> $items
     *
     * @return array{declarations: int, pledges: int, sectors: int, sourced_percent: string}
     */
    public static function fromItems(array $items, ?string $locale = null): array
    {
        $locale ??= SiteContext::locale();
        $declarations = 0;
        $pledges      = 0;

        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (($row['list_section'] ?? '') !== DeclarationItemModel::SECTION_DECLARATIONS) {
                continue;
            }
            $declarations++;
            $kind = (string) ($row['kind'] ?? '');
            if ($kind === DeclarationItemModel::KIND_PLEDGE) {
                $pledges++;
            }
        }

        $sectors = 0;
        if (\Config\Database::connect()->tableExists('sectors')) {
            $sectors = count(model(SectorModel::class)->listOrdered());
        }

        return [
            'declarations'    => $declarations,
            'pledges'         => $pledges,
            'sectors'         => $sectors,
            'sourced_percent' => '100%',
        ];
    }
}
