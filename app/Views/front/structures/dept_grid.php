<?php

declare(strict_types=1);

/**
 * Grille départements / fonctions (table structure_units).
 *
 * @var list<array<string, mixed>> $units
 */

use App\Models\StructureUnitModel;

$model = model(StructureUnitModel::class);
?>
<div class="dept-grid">
    <?php foreach ($units as $idx => $row) :
        $title = trim($model->titleForRow($row));
        $description = trim($model->descriptionForRow($row));
        $email = trim((string) ($row['contact_email'] ?? ''));
        if ($title === '' && $description === '' && $email === '') {
            continue;
        }
        $href = $email !== '' ? 'mailto:' . $email : '';
        $tag  = $href !== '' ? 'a' : 'article';
        $iconUrl = $model->iconUrlForRow($row);
        $iconAlt = $model->iconAltForRow($row);
        ?>
        <<?= $tag ?><?= $href !== '' ? ' href="' . esc($href, 'attr') . '"' : '' ?> class="dept-card reveal" data-delay="<?= (int) $idx * 50 ?>">
            <?php if ($iconUrl !== null) : ?>
                <div class="dept-card__icon">
                    <img src="<?= esc($iconUrl, 'attr') ?>" alt="<?= esc($iconAlt, 'attr') ?>" width="40" height="40" loading="lazy">
                </div>
            <?php endif; ?>
            <?php if ($title !== '') : ?>
                <h4 class="dept-card__title"><?= esc($title) ?></h4>
            <?php endif; ?>
            <?php if ($description !== '') : ?>
                <p class="dept-card__desc"><?= esc($description) ?></p>
            <?php endif; ?>
            <?php if ($email !== '') : ?>
                <span class="dept-card__mail"><?= esc($email) ?></span>
            <?php endif; ?>
        </<?= $tag ?>>
    <?php endforeach; ?>
</div>
