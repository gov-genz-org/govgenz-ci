<?php

declare(strict_types=1);

$flashMessage = session()->getFlashdata('message');
$flashError   = session()->getFlashdata('error');
$flashErrors  = session()->getFlashdata('errors');
?>
<?php if ($flashMessage) : ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="status">
        <?= esc($flashMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('Admin.ui_close'), 'attr') ?>"></button>
    </div>
<?php endif; ?>
<?php if ($flashError) : ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <?= esc($flashError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('Admin.ui_close'), 'attr') ?>"></button>
    </div>
<?php endif; ?>
<?php if ($flashErrors) : ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <?php foreach ((array) $flashErrors as $err) : ?>
            <div><?= esc(is_array($err) ? implode(' ', $err) : $err) ?></div>
        <?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= esc(lang('Admin.ui_close'), 'attr') ?>"></button>
    </div>
<?php endif; ?>
