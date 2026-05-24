<?php

declare(strict_types=1);

/** @var string|null $publicPreviewUrl */
/** @var array{editUrl: string, publicUrl: ?string, viewLabel: string, editLabel: string}|null $translationPartnerNav */
?>
<?php if ($publicPreviewUrl !== null || $translationPartnerNav !== null) : ?>
    <p class="mb-3 d-flex flex-wrap gap-2">
        <?php if ($publicPreviewUrl !== null) : ?>
            <a href="<?= esc($publicPreviewUrl, 'attr') ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"><?= esc(lang('Admin.action_view_published_record')) ?></a>
        <?php endif; ?>
        <?php if ($translationPartnerNav !== null) : ?>
            <a href="<?= esc($translationPartnerNav['editUrl'], 'attr') ?>" class="btn btn-sm btn-outline-secondary"><?= esc($translationPartnerNav['editLabel']) ?></a>
            <?php if ($translationPartnerNav['publicUrl'] !== null) : ?>
                <a href="<?= esc($translationPartnerNav['publicUrl'], 'attr') ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"><?= esc($translationPartnerNav['viewLabel']) ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </p>
<?php endif; ?>
