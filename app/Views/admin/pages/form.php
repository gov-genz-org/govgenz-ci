<?php

declare(strict_types=1);

helper(['admin', 'cms']);

$layoutState = cms_layout_select_state(old('layout_key', $page !== null ? ($page['layout_key'] ?? '') : ''));

$contentMode = $contentMode ?? 'html';
$blocksForForm = $blocksForForm ?? [];

$action = $page
    ? site_url('admin/pages/update/' . $page['id'])
    : site_url('admin/pages/store');

$previewUrl = null;
if ($page !== null && ($page['status'] ?? '') === 'published') {
    $previewUrl = admin_public_page_url((string) ($page['slug'] ?? ''), (string) ($page['locale'] ?? 'fr'));
}
?>
<h1 class="h3 mb-1"><?= $page ? 'Éditer la page' : 'Nouvelle page' ?></h1>
<p class="text-muted small mb-3">Édition par <strong>éditeur HTML</strong> ou par <strong>blocs structurés</strong> (titres, indicateurs, boutons) sans toucher aux classes CSS.</p>

<?php if ($previewUrl !== null) : ?>
    <div class="alert alert-light border py-2 small mb-3">
        <strong>Aperçu public :</strong>
        <a href="<?= esc($previewUrl) ?>" target="_blank" rel="noopener" class="ms-1"><?= esc($previewUrl) ?></a>
    </div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" accept-charset="UTF-8" class="admin-editor-form">
    <?= csrf_field() ?>
    <?php if ($page !== null) : ?>
        <div class="alert alert-secondary border py-2 small mb-3">
            <p class="mb-2"><strong>Aperçu</strong></p>
            <ul class="mb-3 ps-3">
                <li><strong>Aperçu sans enregistrer</strong> — nouvel onglet avec le contenu actuel du formulaire (y compris TinyMCE).</li>
                <li><strong>Aperçu version enregistrée</strong> — ce qui est déjà en base (utile après un enregistrement).</li>
            </ul>
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary" formaction="<?= site_url('admin/pages/preview-draft/' . (int) $page['id']) ?>" formmethod="post" formtarget="_blank">
                    Aperçu sans enregistrer
                </button>
                <a href="<?= site_url('admin/pages/preview/' . (int) $page['id']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark">Aperçu version enregistrée</a>
            </div>
        </div>
    <?php endif; ?>
    <?php
    $slugField = strtolower(trim((string) old('slug', $page !== null ? (string) ($page['slug'] ?? '') : '')));
    $isProjectsProgramCmsSlug = in_array($slugField, ['projets-programme', 'projects-program'], true);
    $isPositionsProgramCmsSlug = in_array($slugField, ['positions-programme', 'positions-program'], true);
    ?>
    <div class="mb-3">
        <label class="form-label" for="slug">Slug (lettres minuscules et tirets)</label>
        <input type="text" name="slug" id="slug" class="form-control" value="<?= esc(old('slug', $page !== null ? $page['slug'] : '')) ?>" required>
        <?php if ($isProjectsProgramCmsSlug) : ?>
            <div class="alert alert-info border py-2 small mt-2 mb-0" role="status">
                <strong>Page « programme projets » :</strong> ce slug est celui attendu par le site pour le bandeau de la
                <strong>liste</strong> des projets (<code>/projects</code> ou <code>/en/projects</code> selon la config), pas l’URL de cette page CMS.
                <?php
                $lpFr = admin_public_projects_program_list_url('fr');
                $lpEn = admin_public_projects_program_list_url('en');
                ?>
                <span class="d-block mt-2">
                    <a href="<?= esc($lpFr, 'attr') ?>" target="_blank" rel="noopener" class="me-2">Voir la liste publique (FR)</a>
                    <a href="<?= esc($lpEn, 'attr') ?>" target="_blank" rel="noopener">Voir la liste publique (EN)</a>
                </span>
            </div>
        <?php elseif ($isPositionsProgramCmsSlug) : ?>
            <div class="alert alert-info border py-2 small mt-2 mb-0" role="status">
                <strong>Page « programme positions » :</strong> bandeau de la liste <code>/positions</code> (ou <code>/en/positions</code>), pas l’URL de cette page CMS.
                <?php
                $lpFr = admin_public_positions_program_list_url('fr');
                $lpEn = admin_public_positions_program_list_url('en');
                ?>
                <span class="d-block mt-2">
                    <a href="<?= esc($lpFr, 'attr') ?>" target="_blank" rel="noopener" class="me-2">Voir la liste publique (FR)</a>
                    <a href="<?= esc($lpEn, 'attr') ?>" target="_blank" rel="noopener">Voir anglais (liste)</a>
                </span>
            </div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <?php $pageLocale = old('locale', $page !== null ? (string) ($page['locale'] ?? 'fr') : 'fr'); ?>
        <?php if (! in_array($pageLocale, ['fr', 'en'], true)) {
            $pageLocale = 'fr';
        } ?>
        <label class="form-label" for="locale">Langue</label>
        <select name="locale" id="locale" class="form-select" style="max-width:16rem">
            <option value="fr" <?= $pageLocale === 'fr' ? 'selected' : '' ?>>Français (sans préfixe d’URL)</option>
            <option value="en" <?= $pageLocale === 'en' ? 'selected' : '' ?>>English (/en/…)</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="translation_group">Groupe de traduction</label>
        <input type="text" name="translation_group" id="translation_group" class="form-control" maxlength="64" value="<?= esc(old('translation_group', $page !== null ? (string) ($page['translation_group'] ?? '') : '')) ?>" autocomplete="off">
        <div class="form-text">Même identifiant pour lier les versions FR/EN d’une même page. Optionnel : un groupe est auto-attribué à la création, et le bouton « Dupliquer trad » le reprend automatiquement.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="title">Titre</label>
        <input type="text" name="title" id="title" class="form-control" value="<?= esc(old('title', $page !== null ? $page['title'] : '')) ?>" required>
    </div>

    <div class="border rounded p-3 mb-3 bg-light-subtle">
        <p class="fw-semibold mb-2">Hero éditorial (optionnel)</p>
        <p class="text-muted small mb-3">Bandeau au-dessus du corps : textes et image ne sont pas dans le HTML ci‑dessous. Si tout est vide, le comportement du site reste inchangé (bandeau compact ou gabarit dans le corps selon la page).</p>
        <div class="mb-3">
            <label class="form-label" for="hero_overline">Sur-titre (petites capitales)</label>
            <input type="text" name="hero_overline" id="hero_overline" class="form-control" maxlength="255" value="<?= esc(old('hero_overline', $page !== null ? (string) ($page['hero_overline'] ?? '') : '')) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label" for="hero_title">Titre affiché en très grand</label>
            <input type="text" name="hero_title" id="hero_title" class="form-control" maxlength="255" value="<?= esc(old('hero_title', $page !== null ? (string) ($page['hero_title'] ?? '') : '')) ?>">
            <div class="form-text">Si vide, aucun grand titre n’est affiché dans le hero.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="hero_lead">Chapô</label>
            <textarea name="hero_lead" id="hero_lead" class="form-control" rows="3"><?= esc(old('hero_lead', $page !== null ? (string) ($page['hero_lead'] ?? '') : '')) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label" for="hero_image_id">ID média (image)</label>
            <input type="number" name="hero_image_id" id="hero_image_id" class="form-control" style="max-width:12rem" min="1" step="1" value="<?= esc(old('hero_image_id', $page !== null && ! empty($page['hero_image_id']) ? (string) $page['hero_image_id'] : '')) ?>">
            <div class="form-text">Identifiant listé dans la <a href="<?= site_url('admin/media') ?>" target="_blank" rel="noopener">médiathèque</a>. Laissez vide pour aucune image.</div>
        </div>
        <div class="mb-0">
            <label class="form-label" for="hero_image_alt">Texte alternatif de l’image</label>
            <input type="text" name="hero_image_alt" id="hero_image_alt" class="form-control" maxlength="255" value="<?= esc(old('hero_image_alt', $page !== null ? (string) ($page['hero_image_alt'] ?? '') : '')) ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Contenu principal</label>
        <div class="btn-group flex-wrap" role="group" aria-label="Mode de contenu">
            <input type="radio" class="btn-check" name="content_mode" id="cm_html" value="html" autocomplete="off" <?= $contentMode === 'html' ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary btn-sm" for="cm_html">Éditeur riche (HTML)</label>
            <input type="radio" class="btn-check" name="content_mode" id="cm_blocks" value="blocks" autocomplete="off" <?= $contentMode === 'blocks' ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary btn-sm" for="cm_blocks">Blocs structurés</label>
        </div>
        <div class="form-text">Basculer efface le champ masqué en base à l’enregistrement (HTML vidé en mode blocs, JSON vidé en mode HTML).</div>
    </div>

    <div id="cms-html-panel" class="<?= $contentMode === 'blocks' ? 'd-none' : '' ?>">
        <div class="mb-3">
            <label class="form-label" for="body_html">Contenu HTML</label>
            <textarea name="body_html" id="body_html" class="form-control" rows="14"><?= old('body_html', $page !== null ? ($page['body_html'] ?? '') : '') ?></textarea>
            <div class="form-text">Éditeur visuel : bouton <strong>Blocs</strong> pour insérer des gabarits dans le HTML ; menu <strong>Formats → Typo GovGenZ</strong>. À l’ouverture de <strong>Code source</strong>, le HTML est automatiquement indenté pour lecture (vous pouvez l’éditer puis Enregistrer). <strong>Alt</strong> obligatoire sur les images.</div>
        </div>
    </div>

    <?= view('admin/pages/blocks_builder', ['blocksForForm' => $blocksForForm, 'contentMode' => $contentMode]) ?>

    <div class="mb-3">
        <label class="form-label" for="status">Statut</label>
        <select name="status" id="status" class="form-select">
            <?php
            $st = old('status', $page !== null ? $page['status'] : 'draft');
            ?>
            <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>>Brouillon</option>
            <option value="published" <?= $st === 'published' ? 'selected' : '' ?>>Publié</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="layout_key">Mise en page sur le site public</label>
        <select name="layout_key" id="layout_key" class="form-select" style="max-width: 28rem;">
            <?php if ($layoutState['legacy']) : ?>
                <option value="<?= esc($layoutState['value'], 'attr') ?>" selected><?= esc($layoutState['value']) ?> (personnalisé — choisir une option standard ci-dessous pour remplacer)</option>
            <?php endif; ?>
            <option value="" <?= ! $layoutState['legacy'] && $layoutState['value'] === '' ? 'selected' : '' ?>>Par défaut (~960&nbsp;px)</option>
            <option value="narrow" <?= ! $layoutState['legacy'] && $layoutState['value'] === 'narrow' ? 'selected' : '' ?>>Étroit</option>
            <option value="full" <?= ! $layoutState['legacy'] && $layoutState['value'] === 'full' ? 'selected' : '' ?>>Pleine largeur</option>
        </select>
        <div class="form-text">Largeur du bloc de contenu pour les visiteurs. Les valeurs personnalisées héritées d’anciennes saisies restent possibles jusqu’à ce que vous sélectionniez une option standard.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="meta_title">Meta title</label>
        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= esc(old('meta_title', $page !== null ? ($page['meta_title'] ?? '') : '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="meta_description">Meta description</label>
        <textarea name="meta_description" id="meta_description" class="form-control" rows="2"><?= esc(old('meta_description', $page !== null ? ($page['meta_description'] ?? '') : '')) ?></textarea>
    </div>

    <div class="admin-form-actions">
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="<?= site_url('admin/pages') ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
