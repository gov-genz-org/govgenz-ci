<?php

declare(strict_types=1);

if (! function_exists('admin_translation_partner_nav')) {
    /**
     * Lien vers la variante FR/EN liée (même translation_group) pour l’écran d’édition admin.
     *
     * @param array<string, mixed>|null $item
     *
     * @return array{editUrl: string, publicUrl: ?string, viewLabel: string, editLabel: string}|null
     */
    function admin_translation_partner_nav(?array $item, string $modelClass, string $adminEditBasePath): ?array
    {
        if ($item === null) {
            return null;
        }

        $tg = trim((string) ($item['translation_group'] ?? ''));
        if ($tg === '') {
            return null;
        }

        $loc = strtolower(trim((string) ($item['locale'] ?? 'fr')));
        if (! in_array($loc, ['fr', 'en'], true)) {
            $loc = 'fr';
        }
        $other = $loc === 'fr' ? 'en' : 'fr';

        $partner = model($modelClass)
            ->where('translation_group', $tg)
            ->where('locale', $other)
            ->first();

        if ($partner === null || ! is_array($partner)) {
            return null;
        }

        $partnerId = (int) ($partner['id'] ?? 0);
        if ($partnerId <= 0) {
            return null;
        }

        $editUrl = site_url(rtrim($adminEditBasePath, '/') . '/edit/' . $partnerId);

        $publicUrl = null;
        $pubState  = strtolower(trim((string) ($partner['publication_state'] ?? $partner['status'] ?? '')));
        $isPub     = $pubState === 'published';

        if ($isPub) {
            $partnerSlug = trim((string) ($partner['slug'] ?? ''));
            if ($partnerSlug !== '') {
                if ($modelClass === \App\Models\PositionItemModel::class) {
                    $publicUrl = admin_public_position_url($partnerSlug, $other);
                } elseif ($modelClass === \App\Models\ProjectProjectModel::class) {
                    $publicUrl = admin_public_project_url($partnerSlug, $other);
                } elseif ($modelClass === \App\Models\CmsPostModel::class) {
                    $publicUrl = admin_public_press_url($partnerSlug, $other);
                } elseif ($modelClass === \App\Models\CmsPageModel::class) {
                    $publicUrl = admin_public_page_url($partnerSlug, $other);
                }
            }
        }

        $viewLabel = $other === 'en' ? lang('Admin.translation_view_en') : lang('Admin.translation_view_fr');
        $editLabel = $other === 'en' ? lang('Admin.translation_edit_en') : lang('Admin.translation_edit_fr');

        return [
            'editUrl'   => $editUrl,
            'publicUrl' => $publicUrl,
            'viewLabel' => $viewLabel,
            'editLabel' => $editLabel,
        ];
    }
}
