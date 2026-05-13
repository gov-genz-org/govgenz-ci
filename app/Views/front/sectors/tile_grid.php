<?php

declare(strict_types=1);

/**
 * Grille seule (à injecter dans une page CMS ou sur la page Secteurs dédiée).
 *
 * @var list<array<string, mixed>> $sectors
 */

use App\Models\SectorModel;

$sectorModel = model(SectorModel::class);
?>
<div class="tile-grid">
    <?php foreach ($sectors as $idx => $row) :
        $code  = (string) ($row['code'] ?? '');
        $email = trim((string) ($row['contact_email'] ?? ''));
        if ($code === '' || $email === '') {
            continue;
        }
        $delay       = (int) $idx * 40;
        $label       = $sectorModel->labelForRow($row);
        $codeDisplay = strtoupper($code);
        ?>
        <a href="mailto:<?= esc($email, 'attr') ?>" class="tile reveal" data-delay="<?= (int) $delay ?>">
            <div class="tile__name"><?= esc($codeDisplay) ?></div>
            <div class="tile__sub"><?= esc($label) ?></div>
            <div class="tile__mail"><?= esc($email) ?></div>
        </a>
    <?php endforeach; ?>
</div>
