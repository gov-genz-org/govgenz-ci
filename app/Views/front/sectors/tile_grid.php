<?php

declare(strict_types=1);

/**
 * Grille seule (à injecter dans une page CMS ou sur la page Secteurs dédiée).
 *
 * @var list<array<string, mixed>> $sectors
 * @var string $layout compact|wide
 */

use App\Models\SectorModel;

$sectorModel = model(SectorModel::class);
$layout = strtolower(trim((string) ($layout ?? 'compact')));
if (! in_array($layout, ['compact', 'wide'], true)) {
    $layout = 'compact';
}
$gridClass = $layout === 'wide' ? 'tile-grid tile-grid--wide' : 'tile-grid tile-grid--compact';
?>
<div class="<?= esc($gridClass, 'attr') ?>">
    <?php foreach ($sectors as $idx => $row) :
        $code  = (string) ($row['code'] ?? '');
        $email = trim((string) ($row['contact_email'] ?? ''));
        if ($code === '' || $email === '') {
            continue;
        }
        $delay       = (int) $idx * 40;
        $label       = $sectorModel->labelForRow($row);
        $codeDisplay = strtoupper($code);
        $iconUrl     = $sectorModel->iconUrlForRow($row);
        $iconAlt     = $sectorModel->iconAltForRow($row);
        ?>
        <a href="mailto:<?= esc($email, 'attr') ?>" class="tile reveal" data-delay="<?= (int) $delay ?>">
            <?php if ($iconUrl !== null) : ?>
                <div class="tile__icon">
                    <img src="<?= esc($iconUrl, 'attr') ?>" alt="<?= esc($iconAlt, 'attr') ?>" width="40" height="40" loading="lazy">
                </div>
            <?php endif; ?>
            <div class="tile__name"><?= esc($codeDisplay) ?></div>
            <div class="tile__sub"><?= esc($label) ?></div>
            <div class="tile__mail"><?= esc($email) ?></div>
        </a>
    <?php endforeach; ?>
</div>
