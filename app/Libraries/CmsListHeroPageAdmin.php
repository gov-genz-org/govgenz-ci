<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\CmsPageModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Pages CMS « bandeau de liste » (press, projects, positions) — admin uniquement.
 */
final class CmsListHeroPageAdmin
{
    public const KIND_PRESS = 'press';

    public const KIND_PROJECTS = 'projects';

    public const KIND_POSITIONS = 'positions';

    /** @var list<string> */
    public const KINDS = [self::KIND_PRESS, self::KIND_PROJECTS, self::KIND_POSITIONS];

    public static function normalizeCreateKind(?string $raw): ?string
    {
        $raw = strtolower(trim((string) ($raw ?? '')));

        return in_array($raw, self::KINDS, true) ? $raw : null;
    }

    public static function translationGroup(string $kind): string
    {
        return match ($kind) {
            self::KIND_PRESS     => 'press-program-list',
            self::KIND_PROJECTS  => 'projects-program-list',
            self::KIND_POSITIONS => 'positions-program-list',
            default              => '',
        };
    }

    public static function canonicalSlug(string $kind): string
    {
        return match ($kind) {
            self::KIND_PRESS     => self::KIND_PRESS,
            self::KIND_PROJECTS  => self::KIND_PROJECTS,
            self::KIND_POSITIONS => self::KIND_POSITIONS,
            default              => '',
        };
    }

    /**
     * @param array<string, mixed>|null $page
     */
    public static function formKind(?array $page, ?string $createKind): ?string
    {
        helper('cms');

        if ($page !== null) {
            $fromSlug = cms_list_hero_page_kind((string) ($page['slug'] ?? ''));
            if ($fromSlug !== null) {
                return $fromSlug;
            }
        }

        return self::normalizeCreateKind($createKind);
    }

    /**
     * @param array<string, mixed>|null $page
     *
     * @return array{editUrl: string, isCreate: bool}|null
     */
    public static function adminUrlForLocale(string $kind, string $locale): ?array
    {
        if (! in_array($kind, self::KINDS, true)) {
            return null;
        }

        $locale = $locale === 'en' ? 'en' : 'fr';
        $slug   = self::canonicalSlug($kind);
        $row    = model(CmsPageModel::class)
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->first();

        if (is_array($row) && isset($row['id'])) {
            return [
                'editUrl'   => site_url('admin/pages/edit/' . (int) $row['id']),
                'isCreate'  => false,
            ];
        }

        return [
            'editUrl'  => site_url('admin/pages/create?list_hero=' . rawurlencode($kind) . '&locale=' . $locale),
            'isCreate' => true,
        ];
    }

    /**
     * Groupe de traduction existant (autre locale) pour préremplir à la création.
     */
    public static function existingTranslationGroup(string $kind): ?string
    {
        $slug = self::canonicalSlug($kind);
        $row  = model(CmsPageModel::class)->where('slug', $slug)->first();
        if (! is_array($row)) {
            return self::translationGroup($kind) ?: null;
        }

        $t = trim((string) ($row['translation_group'] ?? ''));

        return $t !== '' ? $t : self::translationGroup($kind);
    }

    /**
     * @return array<string, mixed>
     */
    public static function payloadForSave(string $kind, IncomingRequest $request, array $hero): array
    {
        return [
            'slug'              => self::canonicalSlug($kind),
            'content_mode'      => 'html',
            'body_html'         => '',
            'body_blocks'       => null,
            'layout_key'        => 'full',
            'hero_overline'     => $hero['hero_overline'],
            'hero_title'        => $hero['hero_title'],
            'hero_lead'         => $hero['hero_lead'],
            'hero_image_id'     => null,
            'hero_image_alt'    => null,
            'translation_group' => self::resolveTranslationGroup($kind, $request),
        ];
    }

    private static function resolveTranslationGroup(string $kind, IncomingRequest $request): string
    {
        $posted = LocaleSlug::normalizeTranslationGroup($request->getPost('translation_group'));
        if ($posted !== null && $posted !== '') {
            return $posted;
        }

        $existing = self::existingTranslationGroup($kind);

        return $existing ?? self::translationGroup($kind);
    }
}
