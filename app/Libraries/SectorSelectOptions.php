<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\SectorModel;

/**
 * Options secteur pour selects / pills (clés en minuscules).
 */
final class SectorSelectOptions
{
    /**
     * @return array<string, string>
     */
    public static function normalizedForSelect(SectorModel $sectorModel): array
    {
        $out = [];
        foreach ($sectorModel->optionsForSelect() as $k => $v) {
            $out[strtolower((string) $k)] = (string) $v;
        }

        return $out;
    }
}
