<?php

declare(strict_types=1);

helper(['declaration', 'language']);

use App\Libraries\SiteContext;

/** @var list<array<string, mixed>> $items */

$loc = SiteContext::locale();
?>
<div class="decl-cards" role="list">
    <?php foreach ($items as $i => $row) :
        $slug = (string) ($row['slug'] ?? '');
        if ($slug === '') {
            continue;
        }
        $detailUrl = declaration_public_url($slug);
        $kind      = (string) ($row['kind'] ?? 'official');
        $tone      = declaration_kind_band_class($kind);
        $band      = trim((string) ($row['band_label'] ?? ''));
        if ($band === '') {
            $band = declaration_default_band_label($kind, $loc);
        }
        $meta      = declaration_format_published_meta((string) ($row['published_at'] ?? ''), (string) ($row['meta_line'] ?? ''), $loc);
        $title     = (string) ($row['title'] ?? '');
        $summary   = trim((string) ($row['summary'] ?? ''));
        $badge     = trim((string) ($row['badge_label'] ?? ''));
        if ($badge === '') {
            $badge = declaration_default_band_label($kind, $loc);
        }
        $titleId   = 'decl-card-title-' . (int) $i;
        ?>
        <article class="decl-card decl-card--<?= esc($tone, 'attr') ?> decl-card--linked reveal" role="listitem" data-delay="<?= (int) $i * 100 ?>">
            <a
                href="<?= esc($detailUrl, 'attr') ?>"
                class="decl-card__hit"
                aria-labelledby="<?= esc($titleId, 'attr') ?>"
            ></a>
            <div class="decl-band"><?= esc($band) ?></div>
            <div class="decl-header">
                <?php if ($meta !== '') : ?>
                    <div class="decl-meta"><span>📅 <?= esc($meta) ?></span></div>
                <?php endif; ?>
                <?php if ($title !== '') : ?>
                    <h3 id="<?= esc($titleId, 'attr') ?>" class="decl-title"><?= esc($title) ?></h3>
                <?php endif; ?>
                <?php if ($summary !== '') : ?>
                    <p class="decl-summary"><?= esc($summary) ?></p>
                <?php endif; ?>
            </div>
            <div class="decl-footer">
                <span class="decl-badge decl-badge--<?= esc($tone, 'attr') ?>"><?= esc($badge) ?></span>
                <div class="decl-footer-actions">
                    <span class="decl-action decl-action--primary" aria-hidden="true"><?= esc(lang('Declaration.card_read_declaration')) ?> →</span>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
