<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;

/**
 * Contexte site public : locale active et segments d’URL après préfixe éventuel /en.
 *
 * `projects` = même appli servie sur le vhost configuré par app.projectsHost (dossier B FTP).
 * `positions` = idem pour app.positionsHost (futur sous-domaine).
 */
final class SiteContext
{
    public const SITE_MAIN = 'main';

    public const SITE_PROJECTS = 'projects';

    public const SITE_POSITIONS = 'positions';

    private static string $siteId = self::SITE_MAIN;

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
        self::$siteId = self::SITE_MAIN;
    }

    public static function setProjects(): void
    {
        self::$siteId = self::SITE_PROJECTS;
    }

    public static function setPositions(): void
    {
        self::$siteId = self::SITE_POSITIONS;
    }

    public static function isProjectsSite(): bool
    {
        return self::$siteId === self::SITE_PROJECTS;
    }

    public static function isPositionsSite(): bool
    {
        return self::$siteId === self::SITE_POSITIONS;
    }

    /**
     * HTTP_HOST correspond à app.projectsHost (insensible à la casse).
     *
     * Tolère l’écart fréquent en local : .env = projects.localhost:8082 alors que le navigateur
     * envoie seulement projects.localhost — on accepte si SERVER_PORT = 8082.
     */
    public static function httpHostMatchesProjectsHost(?RequestInterface $request = null): bool
    {
        return self::httpHostMatchesConfiguredHost('app.projectsHost', $request);
    }

    private static function httpHostMatchesConfiguredHost(string $envKey, ?RequestInterface $request = null): bool
    {
        if (is_cli()) {
            return false;
        }

        $raw = trim($request !== null
            ? (string) $request->getServer('HTTP_HOST')
            : (string) ($_SERVER['HTTP_HOST'] ?? ''));

        $configured = trim((string) env($envKey, ''));
        if ($raw === '' || $configured === '') {
            return false;
        }

        if (strcasecmp($raw, $configured) === 0) {
            return true;
        }

        $rawParts = self::splitHostAndPort($raw);
        $cfgParts = self::splitHostAndPort($configured);

        if (strcasecmp($rawParts['host'], $cfgParts['host']) !== 0) {
            return false;
        }

        if ($cfgParts['port'] === null) {
            return true;
        }

        if ($rawParts['port'] !== null) {
            return (string) $rawParts['port'] === (string) $cfgParts['port'];
        }

        $serverPort = isset($_SERVER['SERVER_PORT']) ? (string) $_SERVER['SERVER_PORT'] : '';

        return $serverPort !== '' && (string) $cfgParts['port'] === $serverPort;
    }

    public static function httpHostMatchesPositionsHost(?RequestInterface $request = null): bool
    {
        return self::httpHostMatchesConfiguredHost('app.positionsHost', $request);
    }

    /**
     * @return array{host: string, port: ?string}
     */
    private static function splitHostAndPort(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return ['host' => '', 'port' => null];
        }

        if (preg_match('#^\[([^\]]+)]:(\d+)$#', $value, $m)) {
            return ['host' => '[' . $m[1] . ']', 'port' => $m[2]];
        }

        $parts = explode(':', $value, 2);

        return [
            'host' => $parts[0],
            'port' => isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null,
        ];
    }

    /**
     * Développement sur un seul domaine : URLs /projects/… (prépare la future bascule sous-domaine).
     */
    public static function projectsPathPrefixEnabled(): bool
    {
        return filter_var(env('app.projectsUsePathPrefix', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function positionsPathPrefixEnabled(): bool
    {
        return filter_var(env('app.positionsUsePathPrefix', true), FILTER_VALIDATE_BOOLEAN);
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
