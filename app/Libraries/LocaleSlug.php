<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Normalisation locale / slug / groupe de traduction (admin CMS & programmes).
 */
final class LocaleSlug
{
    /** @var list<string> */
    private const LOCALES = ['fr', 'en'];

    public static function normalizeLocale(?string $raw): string
    {
        $locale = strtolower(trim((string) $raw));

        return in_array($locale, self::LOCALES, true) ? $locale : 'fr';
    }

    /**
     * Slug URL : minuscules, tirets, sans caractères spéciaux.
     */
    public static function normalizeSlug(?string $raw): string
    {
        $s = mb_strtolower(trim((string) $raw), 'UTF-8');
        $s = preg_replace('/[^a-z0-9\-]+/u', '-', $s) ?? '';
        $s = preg_replace('/-+/', '-', $s) ?? '';

        return trim($s, '-');
    }

    public static function normalizeTranslationGroup(mixed $raw): ?string
    {
        $group = trim((string) $raw);

        return $group !== '' ? $group : null;
    }
}
