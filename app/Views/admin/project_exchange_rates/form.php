<?php

declare(strict_types=1);

helper(['form']);

/** @var array<string, mixed> $config */
$labelYear = old('label_year', (string) ($config['label_year'] ?? '2026'));
$usd = old('usd_ariary', (string) ($config['usd_ariary'] ?? '4500'));
$eur = old('eur_ariary', (string) ($config['eur_ariary'] ?? '4900'));
$cny = old('cny_ariary', (string) ($config['cny_ariary'] ?? '620'));
$jpy = old('jpy_ariary', (string) ($config['jpy_ariary'] ?? '30'));
$fcfa = old('fcfa_ariary', (string) ($config['fcfa_ariary'] ?? '7.5'));
$errors = session('errors') ?? [];
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_exchange_rates')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.form_exchange_intro')) ?></p>

<form method="post" action="<?= site_url('admin/project-exchange-rates/update') ?>" class="border rounded bg-white shadow-sm p-3 p-md-4">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="per-label" class="form-label"><?= esc(lang('Admin.form_exchange_label')) ?></label>
            <input type="text" name="label_year" id="per-label" class="form-control <?= isset($errors['label_year']) ? 'is-invalid' : '' ?>"
                   maxlength="32" required value="<?= esc($labelYear) ?>">
            <div class="form-text"><?= esc(lang('Admin.form_exchange_label_hint')) ?></div>
            <?php if (isset($errors['label_year'])) : ?>
                <div class="invalid-feedback"><?= esc($errors['label_year']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="h6 text-uppercase text-muted border-bottom pb-2 mt-4 mb-3"><?= esc(lang('Admin.form_exchange_rates_heading')) ?></h2>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="per-usd" class="form-label"><?= esc(lang('Admin.form_exchange_usd')) ?></label>
            <input type="number" name="usd_ariary" id="per-usd" class="form-control <?= isset($errors['usd_ariary']) ? 'is-invalid' : '' ?>"
                   min="0.01" step="0.01" required value="<?= esc($usd) ?>">
            <?php if (isset($errors['usd_ariary'])) : ?>
                <div class="invalid-feedback"><?= esc($errors['usd_ariary']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="per-eur" class="form-label"><?= esc(lang('Admin.form_exchange_eur')) ?></label>
            <input type="number" name="eur_ariary" id="per-eur" class="form-control <?= isset($errors['eur_ariary']) ? 'is-invalid' : '' ?>"
                   min="0.01" step="0.01" required value="<?= esc($eur) ?>">
            <?php if (isset($errors['eur_ariary'])) : ?>
                <div class="invalid-feedback"><?= esc($errors['eur_ariary']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="per-cny" class="form-label"><?= esc(lang('Admin.form_exchange_cny')) ?></label>
            <input type="number" name="cny_ariary" id="per-cny" class="form-control <?= isset($errors['cny_ariary']) ? 'is-invalid' : '' ?>"
                   min="0.01" step="0.01" required value="<?= esc($cny) ?>">
            <?php if (isset($errors['cny_ariary'])) : ?>
                <div class="invalid-feedback"><?= esc($errors['cny_ariary']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="per-jpy" class="form-label"><?= esc(lang('Admin.form_exchange_jpy')) ?></label>
            <input type="number" name="jpy_ariary" id="per-jpy" class="form-control <?= isset($errors['jpy_ariary']) ? 'is-invalid' : '' ?>"
                   min="0.01" step="0.01" required value="<?= esc($jpy) ?>">
            <?php if (isset($errors['jpy_ariary'])) : ?>
                <div class="invalid-feedback"><?= esc($errors['jpy_ariary']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="per-fcfa" class="form-label"><?= esc(lang('Admin.form_exchange_fcfa')) ?></label>
            <input type="number" name="fcfa_ariary" id="per-fcfa" class="form-control <?= isset($errors['fcfa_ariary']) ? 'is-invalid' : '' ?>"
                   min="0.0001" step="0.0001" required value="<?= esc($fcfa) ?>">
            <?php if (isset($errors['fcfa_ariary'])) : ?>
                <div class="invalid-feedback"><?= esc($errors['fcfa_ariary']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= esc(lang('Admin.action_save')) ?></button>
        <a href="<?= site_url('admin/project-projects') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.form_exchange_back_projects')) ?></a>
    </div>
</form>
