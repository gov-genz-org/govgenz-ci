<?php

/** @var array<string, string> $sectors */
$sectors = $sectors ?? [];
?>
<div class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
    <section class="section section--join" aria-labelledby="join-heading">
        <div class="section__inner">
            <div class="section__header">
                <div class="section__overline">ENGAGEMENT</div>
                <h1 class="section__title" id="join-heading">Rejoindre</h1>
                <p class="section__lead">
                    Indiquez comment vous souhaitez contribuer. Les champs du premier bloc sont obligatoires ; le reste nous aide à vous recontacter plus facilement.
                </p>
            </div>

            <div class="card ggz-page-join">
                <form action="<?= site_url('join') ?>" method="post" accept-charset="UTF-8" class="ggz-form">
                    <?= csrf_field() ?>
                    <div class="ggz-field">
                        <label for="sector">Secteur d’engagement</label>
                        <p class="ggz-field-hint" id="sector-hint">Choisissez le domaine qui correspond le mieux à votre profil.</p>
                        <select name="sector" id="sector" required aria-describedby="sector-hint">
                            <option value="">— Choisir un secteur —</option>
                            <?php foreach ($sectors as $key => $label) : ?>
                                <option value="<?= esc($key, 'attr') ?>" <?= old('sector') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <fieldset class="ggz-fieldset">
                        <legend class="ggz-fieldset-legend">Coordonnées</legend>
                        <div class="ggz-field">
                            <label for="full_name">Nom complet</label>
                            <input type="text" name="full_name" id="full_name" value="<?= esc(old('full_name')) ?>" required autocomplete="name">
                        </div>
                        <div class="ggz-field">
                            <label for="email">Adresse e-mail</label>
                            <input type="email" name="email" id="email" value="<?= esc(old('email')) ?>" required autocomplete="email" inputmode="email">
                        </div>
                    </fieldset>
                    <fieldset class="ggz-fieldset ggz-fieldset--optional">
                        <legend class="ggz-fieldset-legend">Compléments <span class="ggz-optional-tag">facultatif</span></legend>
                        <div class="ggz-field">
                            <label for="phone">Téléphone</label>
                            <input type="text" name="phone" id="phone" value="<?= esc(old('phone')) ?>" autocomplete="tel" inputmode="tel">
                        </div>
                        <div class="ggz-field">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" rows="5"><?= esc(old('message')) ?></textarea>
                        </div>
                    </fieldset>
                    <div class="ggz-form-actions">
                        <button type="submit" class="btn btn-primary">Envoyer ma candidature</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
