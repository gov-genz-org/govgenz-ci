<?php

declare(strict_types=1);

/** @var string $name */
/** @var string $value */
/** @var int $n */
?>
<div class="pp-repeat-row border rounded p-3 bg-light">
    <div class="d-flex gap-2 align-items-start">
        <div class="flex-grow-1 min-w-0">
            <label class="form-label small mb-1"><?= (int) $n > 0 ? 'Paragraphe complémentaire ' . (int) $n : 'Paragraphe complémentaire (optionnel)' ?></label>
            <textarea name="<?= esc($name, 'attr') ?>" class="form-control form-control-sm" rows="3" maxlength="4000"><?= esc($value) ?></textarea>
        </div>
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => 'Retirer ce paragraphe', 'extraClasses' => 'align-self-start']) ?>
    </div>
</div>
