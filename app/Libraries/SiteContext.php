<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Contexte site public : locale active et segments d’URL après préfixe éventuel /en.
 */
final class SiteContext
{
    private static string $siteId = 'main';

    private static string $locale = 'fr';

    /** @var list<string> */
    private static array $publicSegments = [];

    /**
     * Liens du menu public (pré-calculés dans SiteContextFilter).
     *
     * @var list<array{href: string, label: string, match_key: string, css_class: string}>
     */
    private static array $navMainLinks = [];

    public static function setMain(): void
    {
        self::$siteId = 'main';
    }

    public static function id(): string
    {
        return self::$siteId;
    }

    public static function setLocale(string $locale): void
    {
        self::$locale = $locale === 'en' ? 'en' : 'fr';
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    /**
     * @param list<string> $segments Segments de chemin après suppression du préfixe linguistique éventuel.
     */
    public static function setPublicUriSegments(array $segments): void
    {
        self::$publicSegments = array_values(array_filter($segments, static fn ($s): bool => $s !== '' && $s !== null));
    }

    /**
     * @return list<string>
     */
    public static function publicUriSegments(): array
    {
        return self::$publicSegments;
    }

    public static function publicSegment(int $oneBasedIndex): string
    {
        return (string) (self::$publicSegments[$oneBasedIndex - 1] ?? '');
    }

    /**
     * @param list<array{href: string, label: string, match_key: string, css_class: string}> $links
     */
    public static function setNavMainLinks(array $links): void
    {
        self::$navMainLinks = $links;
    }

    /**
     * @return list<array{href: string, label: string, match_key: string, css_class: string}>
     */
    public static function navMainLinks(): array
    {
        return self::$navMainLinks;
    }
}
