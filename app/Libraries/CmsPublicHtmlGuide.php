<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Database\Support\CmsLegalMentionsBodies;

/**
 * Référence des blocs HTML réutilisables (site_govgenz / govgenz-template.css).
 * Utilisé par l’aide admin (aperçu + extraits) — pas de styles inline : tout passe par les classes globales.
 */
final class CmsPublicHtmlGuide
{
    /**
     * @return list<array{id:string,title:string,intro:string,html:string}>
     */
    public static function sections(): array
    {
        return [
            [
                'id'    => 'intro',
                'title' => 'Bonnes pratiques',
                'intro' => 'Préférez ces motifs aux styles inline ou aux classes ad hoc. Une page = une section racine avec fond (ex. section.section--qui, section.section--adn) et un div.section__inner. Pour les mentions légales / cookies (bandeau analytics), voir le bloc « mentions-legales » ci-dessous.',
                'html'  => '',
            ],
            [
                'id'    => 'site-footer',
                'title' => 'Pied de page global (slug réservé site-footer)',
                'intro' => 'Créez deux pages publiées (FR et EN), même slug site-footer, même groupe de traduction. Le corps remplace uniquement les trois colonnes sous le logo ; le logo, la devise et la ligne © restent dans le gabarit. Collez le HTML en mode source (recommandé) avec des blocs .footer__col (sans wrapper .footer__columns, déjà présent dans le layout). Les URLs : adaptez aux slugs de votre locale (/secteurs vs /sectors, etc.).',
                'html'  => <<<'HTML'
<div class="footer__col">
    <h4>Le mouvement</h4>
    <ul>
        <li><a href="/qui-sommes-nous">Qui sommes-nous</a></li>
        <li><a href="/notre-adn">Notre ADN</a></li>
        <li><a href="/structure">Structure</a></li>
        <li><a href="/secteurs">Secteurs</a></li>
        <li><a href="/etude">Étude</a></li>
        <li><a href="/contact">Contact</a></li>
        <li><a href="/press">Presse</a></li>
        <li><a href="/join">Rejoindre</a></li>
    </ul>
</div>
<div class="footer__col">
    <h4>À venir</h4>
    <ul>
        <li><span class="footer__soon">declaration.govgenz.org</span></li>
        <li><span class="footer__soon">counterpoint.govgenz.org</span></li>
        <li><span class="footer__soon">projects.govgenz.org</span></li>
    </ul>
</div>
<div class="footer__col">
    <h4>Contacts</h4>
    <ul>
        <li><a href="mailto:contact@govgenz.org">contact@govgenz.org</a></li>
        <li><a href="mailto:recruitment@govgenz.org">recruitment@govgenz.org</a></li>
        <li><a href="mailto:partnerships@govgenz.org">partnerships@govgenz.org</a></li>
        <li><a href="mailto:ethics@govgenz.org">ethics@govgenz.org</a></li>
    </ul>
</div>
HTML,
            ],
            [
                'id'    => 'legal-mentions',
                'title' => 'Mentions légales (slug mentions-legales)',
                'intro' => 'Page obligatoire pour le lien « En savoir plus » du bandeau cookies (analytics.privacyPageSlug). Créez deux pages publiées : locale FR et EN, slug mentions-legales, groupe legal-mentions. Sur-titre, titre et chapô : champs du formulaire (Sur-titre / Titre affiché / Chapô), pas dans le HTML. Le corps = section.section--legal + div.ggz-legal-prose uniquement (h2, paragraphes, listes). Ne recopiez pas de bloc section__header dans le corps. URL : /mentions-legales et /en/mentions-legales.',
                'html'  => CmsLegalMentionsBodies::guideHtml(),
            ],
            [
                'id'    => 'section-header',
                'title' => 'En-tête de rubrique',
                'intro' => 'Overline pill + titre + chapô. Utiliser h1 sur une page dédiée (sans hero gabarit), h2 dans une maquette multi-sections.',
                'html'  => <<<'HTML'
<div class="section__header">
    <div class="section__overline" data-i18n="ex.overline">SUR-TITRE</div>
    <h2 class="section__title" data-i18n="ex.title">Titre principal</h2>
    <p class="section__lead" data-i18n="ex.lead">Texte d’introduction en italique, une ou deux phrases.</p>
</div>
HTML,
            ],
            [
                'id'    => 'home-program',
                'title' => 'Hero home — Programme Paikady Taninjanaka',
                'intro' => 'Hero landing style site_govgenz (titre en 2 lignes, 2e ligne en dégradé rouge, devise et doubles CTA).',
                'html'  => <<<'HTML'
<section class="hero" aria-labelledby="hero-heading">
    <div class="hero__bg-grid"></div>
    <div class="hero__bg-glow"></div>
    <div class="hero__inner">
        <div class="hero__overline">PROGRAMME PAIKADY TANINJANAKA</div>
        <h1 class="hero__title" id="hero-heading">
            <span class="hero__title-1">GOV GEN Z</span>
            <span class="hero__title-2">MADAGASCAR</span>
        </h1>
        <p class="hero__tagline">Mouvement structuré pour bâtir un avenir digne, serein et durable</p>
        <div class="hero__devise">
            <span class="hero__devise-line">Dignité &amp; sérénité pour le peuple.</span>
            <span class="hero__devise-line">Un avenir meilleur pour la jeunesse et les générations futures.</span>
        </div>
        <div class="section__btn-row">
            <a href="/qui-sommes-nous" class="btn btn--primary">Découvrir le mouvement</a>
            <a href="/contact" class="btn btn--ghost">Nous écrire</a>
        </div>
    </div>
</section>
<div class="section__source">
    <span>Gov Gen Z Madagascar</span>
</div>
HTML,
            ],
            [
                'id'    => 'cercles',
                'title' => 'Cartes « cercles » (qui sommes-nous)',
                'intro' => 'Grille .cercles ; chaque carte est un .cercle avec classe reveal. Attribut data-count sur le chiffre pour l’animation du script.',
                'html'  => <<<'HTML'
<div class="cercles">
    <div class="cercle reveal" data-delay="0">
        <div class="cercle__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 7a4 4 0 100 8 4 4 0 000-8zM4 21v-1a6 6 0 016-6h4a6 6 0 016 6v1"/></svg>
        </div>
        <div class="cercle__number" data-count="12.44">12,44</div>
        <div class="cercle__unit">M</div>
        <h3 class="cercle__title" data-i18n="qui.c1.title">Enfants</h3>
        <p class="cercle__sub" data-i18n="qui.c1.sub">0–17 ans · 48,5%</p>
        <p class="cercle__desc" data-i18n="qui.c1.desc">L’avenir du pays se joue dès aujourd’hui.</p>
    </div>
    <div class="cercle reveal" data-delay="100">
        <div class="cercle__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20M5 9c2 0 4 2 4 4M19 9c-2 0-4 2-4 4M12 12c-2-2-4-2-6 0M12 12c2-2 4-2 6 0"/></svg>
        </div>
        <div class="cercle__number cercle__number--text">∞</div>
        <h3 class="cercle__title" data-i18n="qui.c3.title">Relève</h3>
        <p class="cercle__sub" data-i18n="qui.c3.sub">Générations futures</p>
        <p class="cercle__desc" data-i18n="qui.c3.desc">Bâtir un héritage qui les protège.</p>
    </div>
</div>
HTML,
            ],
            [
                'id'    => 'adn',
                'title' => 'Piliers ADN',
                'intro' => 'Conteneur .adn ; cartes .adn-card.adn-card--1 … --4 avec reveal.',
                'html'  => <<<'HTML'
<div class="adn">
    <article class="adn-card adn-card--1 reveal" data-delay="0">
        <div class="adn-card__num">01</div>
        <div class="adn-card__overline" data-i18n="adn.p1.overline">POUR QUI</div>
        <h3 class="adn-card__title" data-i18n="adn.p1.title">Notre raison d’être</h3>
        <ul class="adn-card__list">
            <li data-i18n="adn.p1.l1">Premier point</li>
            <li data-i18n="adn.p1.l2">Deuxième point</li>
        </ul>
    </article>
</div>
HTML,
            ],
            [
                'id'    => 'structure',
                'title' => 'Structure — hub + fonctions',
                'intro' => 'Bloc .hub avec .hub__core et grille .hub__grid de liens .fn-card.',
                'html'  => <<<'HTML'
<div class="hub">
    <div class="hub__core">
        <div class="hub__label" data-i18n="structure.noyau.label">NOYAU EXÉCUTIF CENTRAL</div>
        <div class="hub__sub" data-i18n="structure.noyau.sub">Coordination · Vision</div>
        <a href="mailto:contact@govgenz.org" class="hub__mail">contact@govgenz.org</a>
    </div>
    <div class="hub__grid">
        <a href="mailto:communication@govgenz.org" class="fn-card reveal" data-delay="0">
            <div class="fn-card__name" data-i18n="fn.com.name">COMMUNICATION</div>
            <div class="fn-card__sub" data-i18n="fn.com.sub">Stratégie · Contenus</div>
            <div class="fn-card__mail">communication@govgenz.org</div>
        </a>
    </div>
</div>
HTML,
            ],
            [
                'id'    => 'secteurs',
                'title' => 'Grille tuiles (contact)',
                'intro' => 'Conteneur .tile-grid, cartes cliquables a.tile. Pour afficher les 14 secteurs depuis la base (table sectors, comme Rejoindre), supprimez les tuiles statiques et insérez en mode source le marqueur décrit dans « Grille secteurs (dynamique BDD) ».',
                'html'  => <<<'HTML'
<div class="tile-grid">
    <a href="mailto:education@govgenz.org" class="tile reveal" data-delay="0">
        <div class="tile__name">EDUCATION</div>
        <div class="tile__sub" data-i18n="sect.education">Formation · Recherche</div>
        <div class="tile__mail">education@govgenz.org</div>
    </a>
</div>
HTML,
            ],
            [
                'id'    => 'secteurs-dynamic',
                'title' => 'Grille secteurs (dynamique BDD)',
                'intro' => 'Les libellés FR/EN et les e-mails viennent de la table sectors (administration > Secteurs). En mode source : même effet pour data-gg-cms="sectors-tile-grid" (nom anglais) ou data-gg-cms="secteurs-tile-grid" (nom français). Commentaires : GG_CMS_SECTORS_TILE_GRID ou GG_CMS_SECTEURS_TILE_GRID.',
                'html'  => <<<'HTML'
<div class="section__inner">
    <div data-gg-cms="sectors-tile-grid"></div>
</div>
<p class="small text-muted">Variante française équivalente : <code>&lt;div data-gg-cms="secteurs-tile-grid"&gt;&lt;/div&gt;</code></p>
HTML,
            ],
            [
                'id'    => 'etude',
                'title' => 'Statistiques + CTA',
                'intro' => 'Bloc .stats avec .stat ; encadré d’appel .section__cta, texte .section__cta-lead, bouton .btn.btn--primary.',
                'html'  => <<<'HTML'
<div class="stats">
    <div class="stat reveal" data-delay="0">
        <div class="stat__num"><span data-count="72.6">72,6</span><span class="stat__suffix">%</span></div>
        <div class="stat__label" data-i18n="etude.s1">Indicateur exemple</div>
    </div>
</div>
<div class="section__cta">
    <p class="section__cta-lead" data-i18n="etude.cta.text">Texte d’appel.</p>
    <a href="mailto:contact@govgenz.org" class="btn btn--primary" data-i18n="etude.cta.btn">Action</a>
</div>
HTML,
            ],
            [
                'id'    => 'contact',
                'title' => 'Carte contact',
                'intro' => 'Rubrique au-dessus de la carte : section__header. Dessous : .contact-card + .contact-grid.',
                'html'  => <<<'HTML'
<div class="section__header">
    <div class="section__overline" data-i18n="contact.overline">REJOINDRE LE MOUVEMENT</div>
    <h1 class="section__title" data-i18n="contact.title">Titre page</h1>
    <p class="section__lead" data-i18n="contact.lead">Accroche.</p>
</div>
<div class="contact-card">
    <div class="contact-card__inner">
        <div class="contact-grid">
            <a href="mailto:contact@govgenz.org" class="contact-block">
                <div class="contact-block__label" data-i18n="contact.b1.label">CONTACT</div>
                <div class="contact-block__mail">contact@govgenz.org</div>
                <div class="contact-block__sub" data-i18n="contact.b1.sub">Sous-texte</div>
            </a>
        </div>
    </div>
</div>
HTML,
            ],
            [
                'id'    => 'press-page',
                'title' => 'Presse — fil d’Ariane + en-tête',
                'intro' => 'Aligné sur les pages /press : navigation .ggz-breadcrumb en premier dans .section__inner, puis .section__header (overline, titre, chapô). À réutiliser dans le corps HTML d’un article si besoin.',
                'html'  => <<<'HTML'
<nav class="ggz-breadcrumb" aria-label="Fil d’Ariane">
    <a href="/">Accueil</a>
    <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
    <a href="/press">Presse</a>
    <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
    <span class="muted">Communiqué</span>
</nav>
<div class="section__header">
    <div class="section__overline">MÉDIAS</div>
    <h1 class="section__title">Titre du communiqué</h1>
    <p class="section__lead">Chapô ou extrait affiché sous le titre.</p>
</div>
HTML,
            ],
            [
                'id'    => 'site-footer-minimal',
                'title' => 'Pied de page — variante courte (2 colonnes)',
                'intro' => 'Si vous ne voulez que deux blocs, laissez simplement 2 .footer__col ; le layout les place dans sa grille. Pour une seule colonne pleine largeur, utilisez un seul .footer__col.',
                'html'  => <<<'HTML'
<div class="footer__col">
    <h4>Liens utiles</h4>
    <ul>
        <li><a href="/contact">Contact</a></li>
        <li><a href="/press">Presse</a></li>
        <li><a href="/join">Rejoindre</a></li>
    </ul>
</div>
<div class="footer__col">
    <h4>Écrivez-nous</h4>
    <ul>
        <li><a href="mailto:contact@govgenz.org">contact@govgenz.org</a></li>
    </ul>
</div>
HTML,
            ],
            [
                'id'    => 'wire-full-section',
                'title' => 'Exemple de section complète',
                'intro' => 'Enveloppe type pour une page pleine largeur CMS.',
                'html'  => <<<'HTML'
<section class="section section--qui" id="exemple" aria-labelledby="ex-h">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline">SECTION</div>
            <h1 class="section__title" id="ex-h">Titre page</h1>
            <p class="section__lead">Chapô.</p>
        </div>
        <!-- … composants … -->
        <div class="section__source"><span>Source · Référence</span></div>
    </div>
</section>
HTML,
            ],
        ];
    }
}
