<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Bundles CSS/JS pour le layout front (évite le HTML inline dans les contrôleurs).
 */
final class FrontPageAssets
{
    public static function projectsProgramList(): string
    {
        return view('front/partials/head_assets/projects_program_list');
    }

    public static function projectsProgramShowHead(): string
    {
        return view('front/partials/head_assets/projects_program_show_head');
    }

    public static function projectsProgramShowScripts(bool $includeFundFormScripts): string
    {
        return view('front/partials/head_assets/projects_program_show_scripts', [
            'includeFundFormScripts' => $includeFundFormScripts,
        ]);
    }

    public static function positionsProgramList(): string
    {
        return view('front/partials/head_assets/positions_program_list');
    }

    /** @return array{head: string, scripts: string} */
    public static function positionsProgramShow(): array
    {
        return [
            'head'    => view('front/partials/head_assets/positions_program_show_head'),
            'scripts' => view('front/partials/head_assets/positions_program_show_scripts'),
        ];
    }
}
