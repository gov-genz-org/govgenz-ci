<?php

declare(strict_types=1);

/**
 * Hub organigramme (noyau + fonctions) depuis structure_units.
 *
 * @var array<string, mixed>|null $core
 * @var list<array<string, mixed>> $functions
 */

use App\Models\StructureUnitModel;

$model = model(StructureUnitModel::class);
?>
<div class="hub">
    <?php if (is_array($core)) :
        $coreTitle = trim($model->titleForRow($core));
        $coreSub   = trim($model->subtitleForRow($core));
        $coreMail  = trim((string) ($core['contact_email'] ?? ''));
        ?>
        <div class="hub__core">
            <?php if ($coreTitle !== '') : ?>
                <div class="hub__label"><?= esc($coreTitle) ?></div>
            <?php endif; ?>
            <?php if ($coreSub !== '') : ?>
                <div class="hub__sub"><?= esc($coreSub) ?></div>
            <?php endif; ?>
            <?php if ($coreMail !== '') : ?>
                <a href="mailto:<?= esc($coreMail, 'attr') ?>" class="hub__mail"><?= esc($coreMail) ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="hub__grid">
        <?php foreach ($functions as $idx => $row) :
            $name = trim($model->titleForRow($row));
            $sub  = trim($model->subtitleForRow($row));
            $mail = trim((string) ($row['contact_email'] ?? ''));
            if ($name === '' && $sub === '' && $mail === '') {
                continue;
            }
            $href = $mail !== '' ? 'mailto:' . $mail : '';
            $tag  = $href !== '' ? 'a' : 'div';
            ?>
            <<?= $tag ?><?= $href !== '' ? ' href="' . esc($href, 'attr') . '"' : '' ?> class="fn-card reveal" data-delay="<?= (int) $idx * 50 ?>">
                <?php if ($name !== '') : ?>
                    <div class="fn-card__name"><?= esc($name) ?></div>
                <?php endif; ?>
                <?php if ($sub !== '') : ?>
                    <div class="fn-card__sub"><?= esc($sub) ?></div>
                <?php endif; ?>
                <?php if ($mail !== '') : ?>
                    <div class="fn-card__mail"><?= esc($mail) ?></div>
                <?php endif; ?>
            </<?= $tag ?>>
        <?php endforeach; ?>
    </div>
</div>
