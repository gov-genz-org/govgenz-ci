<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Titres / méta liste programme depuis une page CMS publiée.
 */
final class CmsProgramListHero
{
    /**
     * @param array<string, mixed>|null $listPage ligne CmsPageModel
     *
     * @return array{heroOverline: string, heroTitle: string, heroLead: string, layoutTitle: string, layoutMeta: string}
     */
    public static function resolve(?array $listPage, string $defaultListTitle, string $defaultLayoutTitle): array
    {
        $heroOverline = '';
        $heroTitle    = $defaultListTitle;
        $heroLead     = '';
        $layoutTitle  = $defaultLayoutTitle;
        $layoutMeta   = '';

        if ($listPage === null) {
            return compact('heroOverline', 'heroTitle', 'heroLead', 'layoutTitle', 'layoutMeta');
        }

        $heroOverline = trim((string) ($listPage['hero_overline'] ?? ''));
        $ht           = trim((string) ($listPage['hero_title'] ?? ''));
        $heroTitle    = $ht !== '' ? $ht : trim((string) ($listPage['title'] ?? ''));
        if ($heroTitle === '') {
            $heroTitle = $defaultListTitle;
        }
        $heroLead = trim((string) ($listPage['hero_lead'] ?? ''));

        $mt = trim((string) ($listPage['meta_title'] ?? ''));
        if ($mt !== '') {
            $layoutTitle = $mt;
        }
        $md = trim((string) ($listPage['meta_description'] ?? ''));
        if ($md !== '') {
            $layoutMeta = $md;
        }

        return compact('heroOverline', 'heroTitle', 'heroLead', 'layoutTitle', 'layoutMeta');
    }
}
