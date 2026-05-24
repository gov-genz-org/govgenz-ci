<?php

declare(strict_types=1);

namespace App\Database\Support;

/**
 * Contenus marketing EN (corps HTML + méta) à partir du slug FR — utilisé par les migrations i18n.
 */
final class CmsGovgenzEnglishMarketingSeed
{
    public static function englishSlug(string $slugFr): ?string
    {
        return match ($slugFr) {
            'home'            => 'home',
            'qui-sommes-nous' => 'who-we-are',
            'notre-adn'       => 'our-dna',
            'structure'       => 'structure',
            'secteurs'        => 'sectors',
            'etude'           => 'study',
            'contact'         => 'contact',
            'presse'          => 'press',
            'rejoignez-nous'  => 'join',
            default           => null,
        };
    }

    public static function englishTitle(string $slugFr, string $titleFr): string
    {
        return match ($slugFr) {
            'home'            => 'GovGenZ — Bridging generations',
            'qui-sommes-nous' => 'Who we are',
            'notre-adn'       => 'Our DNA',
            'structure'       => 'Structure',
            'secteurs'        => 'Sectors',
            'etude'           => 'Youth study',
            'contact'         => 'Contact',
            'presse'          => 'Press',
            'rejoignez-nous'  => 'Join us',
            default           => $titleFr,
        };
    }

    /**
     * @return array{meta_title?: string, meta_description?: string}
     */
    public static function meta(string $slugFr): array
    {
        return match ($slugFr) {
            'home' => [
                'meta_title'       => 'Gov Gen Z Madagascar',
                'meta_description' => 'Paikady Taninjanaka — a citizen movement for youth and Madagascar\'s future.',
            ],
            'qui-sommes-nous' => [
                'meta_title'       => 'Who we are · Gov Gen Z Madagascar',
                'meta_description' => 'Five circles — children, youth, future generations, diaspora and supporters.',
            ],
            'notre-adn' => [
                'meta_title'       => 'Our DNA · Gov Gen Z Madagascar',
                'meta_description' => 'Purpose, values, method and mission — what drives Gov Gen Z Madagascar.',
            ],
            'structure' => [
                'meta_title'       => 'Structure · Gov Gen Z Madagascar',
                'meta_description' => 'Central executive core, seven functions and fourteen sector teams.',
            ],
            'secteurs' => [
                'meta_title'       => 'Sector teams · Gov Gen Z Madagascar',
                'meta_description' => 'Fourteen domains of action — reach each team directly.',
            ],
            'etude' => [
                'meta_title'       => 'Youth study 2026 · Gov Gen Z Madagascar',
                'meta_description' => 'Key figures on Malagasy youth, poverty, schooling and child labour.',
            ],
            'contact' => [
                'meta_title'       => 'Contact · Gov Gen Z Madagascar',
                'meta_description' => 'General contact, join form, partnerships and press.',
            ],
            default => [],
        };
    }

    public static function bodyHtml(string $slugFr): ?string
    {
        helper(['url']);

        return match ($slugFr) {
            'home'            => CmsGovgenzHtmlBodiesEn::home(),
            'qui-sommes-nous' => CmsGovgenzHtmlBodiesEn::quiSommesNous(),
            'notre-adn'       => CmsGovgenzHtmlBodiesEn::notreAdn(),
            'structure'       => CmsGovgenzHtmlBodiesEn::structure(),
            'secteurs'        => CmsGovgenzHtmlBodiesEn::secteurs(),
            'etude'           => CmsGovgenzHtmlBodiesEn::etude(),
            'contact'         => CmsGovgenzHtmlBodiesEn::contact(),
            default           => null,
        };
    }
}
