<?php

declare(strict_types=1);

namespace App\Database\Support;

/**
 * HTML des pages publiques alignées sur site_govgenz (sections + en-têtes).
 */
final class CmsGovgenzHtmlBodies
{
    public static function quiSommesNous(): string
    {
        /*
         * Même composant que site_govgenz (govgenz-template.css → .cercles / .cercle).
         * Pas de styles inline : tout est global dans la feuille template + pont (#main-content … article.wysiwyg).
         */
        $rows = [
            [
                'delay' => 0,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 7a4 4 0 100 8 4 4 0 000-8zM4 21v-1a6 6 0 016-6h4a6 6 0 016 6v1"/></svg>',
                'countAttr' => '12.44',
                'countText' => '12,44',
                'infinity'  => false,
                'unit'      => 'M',
                'titleKey' => 'qui.c1.title',
                'titleFr'  => 'Enfants',
                'subKey'   => 'qui.c1.sub',
                'subFr'    => '0–17 ans · 48,5%',
                'descKey'  => 'qui.c1.desc',
                'descFr'   => 'L\'avenir du pays se joue dès aujourd\'hui',
            ],
            [
                'delay' => 100,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM3 21v-2a4 4 0 014-4h10a4 4 0 014 4v2"/></svg>',
                'countAttr' => '8.68',
                'countText' => '8,68',
                'infinity'  => false,
                'unit'      => 'M',
                'titleKey' => 'qui.c2.title',
                'titleFr'  => 'Jeunesse',
                'subKey'   => 'qui.c2.sub',
                'subFr'    => '14–30 ans · 33,8%',
                'descKey'  => 'qui.c2.desc',
                'descFr'   => 'Cœur de la mobilisation et de la construction',
            ],
            [
                'delay' => 200,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20M5 9c2 0 4 2 4 4M19 9c-2 0-4 2-4 4M12 12c-2-2-4-2-6 0M12 12c2-2 4-2 6 0"/></svg>',
                'countAttr' => '',
                'countText' => '',
                'infinity'  => true,
                'unit'      => '',
                'titleKey' => 'qui.c3.title',
                'titleFr'  => 'Relève',
                'subKey'   => 'qui.c3.sub',
                'subFr'    => 'Générations futures',
                'descKey'  => 'qui.c3.desc',
                'descFr'   => 'Bâtir un héritage qui les protège',
                'infinityLabelKey' => 'qui.c3.label',
            ],
            [
                'delay' => 300,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg>',
                'infinity' => true,
                'unit'     => '',
                'titleKey' => 'qui.c4.title',
                'titleFr'  => 'Diaspora',
                'subKey'   => 'qui.c4.sub',
                'subFr'    => 'Malgaches du monde',
                'descKey'  => 'qui.c4.desc',
                'descFr'   => 'Compétences, mentorat, plaidoyer international',
            ],
            [
                'delay' => 400,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>',
                'infinity' => true,
                'unit'     => '',
                'titleKey' => 'qui.c5.title',
                'titleFr'  => 'Sympathisants',
                'subKey'   => 'qui.c5.sub',
                'subFr'    => 'Celles et ceux qui soutiennent',
                'descKey'  => 'qui.c5.desc',
                'descFr'   => 'Toutes les énergies bienveillantes',
            ],
        ];

        $cards = '';
        foreach ($rows as $r) {
            $delay = (int) $r['delay'];
            $cards .= '<div class="cercle reveal" data-delay="' . $delay . '">';
            $cards .= '<div class="cercle__icon">' . $r['icon'] . '</div>';
            if (! empty($r['infinity'])) {
                $lblKey = $r['infinityLabelKey'] ?? '';
                if ($lblKey !== '') {
                    $cards .= '<div class="cercle__number cercle__number--text" data-i18n="' . esc($lblKey, 'attr') . '">∞</div>';
                } else {
                    $cards .= '<div class="cercle__number cercle__number--text">∞</div>';
                }
            } else {
                $ca = esc((string) ($r['countAttr'] ?? ''), 'attr');
                $ct = esc((string) ($r['countText'] ?? ''));
                $cards .= '<div class="cercle__number" data-count="' . $ca . '">' . $ct . '</div>';
                $cards .= '<div class="cercle__unit">' . esc((string) ($r['unit'] ?? '')) . '</div>';
            }
            $cards .= '<h3 class="cercle__title" data-i18n="' . esc($r['titleKey'], 'attr') . '">' . esc($r['titleFr']) . '</h3>';
            $cards .= '<p class="cercle__sub" data-i18n="' . esc($r['subKey'], 'attr') . '">' . esc($r['subFr']) . '</p>';
            $cards .= '<p class="cercle__desc" data-i18n="' . esc($r['descKey'], 'attr') . '">' . esc($r['descFr']) . '</p>';
            $cards .= '</div>';
        }

        return <<<HTML
<section class="section section--qui" id="qui-content" aria-labelledby="qs-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline" data-i18n="qui.overline">QUI SOMMES-NOUS</div>
            <h1 class="section__title" id="qs-heading" data-i18n="qui.title">Pour eux, avec vous</h1>
            <p class="section__lead" data-i18n="qui.lead">
                Nous nous battons pour cinq cercles concentriques : ceux qui construisent aujourd'hui, ceux qui hériteront demain, et celles et ceux qui nous soutiennent partout dans le monde.
            </p>
        </div>
        <div class="cercles">
            {$cards}
        </div>
        <div class="section__source"><span data-i18n="qui.source">Source · Étude GoV Gen Z Madagascar 2026</span></div>
    </div>
</section>
HTML;
    }

    public static function notreAdn(): string
    {
        $pillars = [
            [
                'delay' => 0,
                'num'   => '01',
                'cls'   => 'adn-card--1',
                'overKey' => 'adn.p1.overline', 'overFr' => 'POUR QUI',
                'titleKey' => 'adn.p1.title', 'titleFr' => 'Notre raison d\'être',
                'lines' => [
                    ['adn.p1.l1', '8,68 M de jeunes (14–30 ans)'],
                    ['adn.p1.l2', '12,44 M d\'enfants (0–17 ans)'],
                    ['adn.p1.l3', 'La diaspora malgache mondiale'],
                    ['adn.p1.l4', 'Les générations futures'],
                ],
            ],
            [
                'delay' => 100,
                'num'   => '02',
                'cls'   => 'adn-card--2',
                'overKey' => 'adn.p2.overline', 'overFr' => 'CE QUI NOUS GUIDE',
                'titleKey' => 'adn.p2.title', 'titleFr' => 'Nos valeurs',
                'lines' => [
                    ['adn.p2.l1', 'Intégrité · Éthique'],
                    ['adn.p2.l2', 'Entraide · Harmonie'],
                    ['adn.p2.l3', 'Vitesse · Efficacité'],
                    ['adn.p2.l4', 'Servir la cause'],
                ],
            ],
            [
                'delay' => 200,
                'num'   => '03',
                'cls'   => 'adn-card--3',
                'overKey' => 'adn.p3.overline', 'overFr' => 'COMMENT',
                'titleKey' => 'adn.p3.title', 'titleFr' => 'Notre méthode',
                'lines' => [
                    ['adn.p3.l1', 'Intelligence collective'],
                    ['adn.p3.l2', 'Co-construction citoyenne'],
                    ['adn.p3.l3', '15% réfléchir · 85% agir'],
                    ['adn.p3.l4', 'Impacts mesurables &amp; utiles'],
                ],
            ],
            [
                'delay' => 300,
                'num'   => '04',
                'cls'   => 'adn-card--4',
                'overKey' => 'adn.p4.overline', 'overFr' => 'POUR QUOI',
                'titleKey' => 'adn.p4.title', 'titleFr' => 'Notre but',
                'lines' => [
                    ['adn.p4.l1', 'Dignité &amp; sérénité'],
                    ['adn.p4.l2', 'Souveraineté nationale'],
                    ['adn.p4.l3', 'Système au service du peuple'],
                    ['adn.p4.l4', 'Avenir meilleur — durable'],
                ],
            ],
        ];

        $blocks = '';
        foreach ($pillars as $p) {
            $lis = '';
            foreach ($p['lines'] as [$k, $fr]) {
                $lis .= '<li data-i18n="' . esc($k, 'attr') . '">' . $fr . '</li>';
            }
            $blocks .= '<article class="adn-card ' . esc($p['cls'], 'attr') . ' reveal" data-delay="' . (int) $p['delay'] . '">';
            $blocks .= '<div class="adn-card__num">' . esc($p['num']) . '</div>';
            $blocks .= '<div class="adn-card__overline" data-i18n="' . esc($p['overKey'], 'attr') . '">' . esc($p['overFr']) . '</div>';
            $blocks .= '<h3 class="adn-card__title" data-i18n="' . esc($p['titleKey'], 'attr') . '">' . esc($p['titleFr']) . '</h3>';
            $blocks .= '<ul class="adn-card__list">' . $lis . '</ul>';
            $blocks .= '</article>';
        }

        return <<<HTML
<section class="section section--adn" id="adn-content" aria-labelledby="adn-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline" data-i18n="adn.overline">L'ADN DE GoV GEN Z MADAGASCAR</div>
            <h1 class="section__title" id="adn-heading" data-i18n="adn.title">Ce qui nous porte</h1>
            <p class="section__lead" data-i18n="adn.lead">
                Quatre piliers qui définissent qui nous sommes, ce que nous voulons, et comment nous y allons.
            </p>
        </div>
        <div class="adn">
            {$blocks}
        </div>
    </div>
</section>
HTML;
    }

    public static function structure(): string
    {
        $fns = [
            ['coordination@govgenz.org', 0, 'fn.coord.name', 'COORDINATION', 'fn.coord.sub', 'Exécutifs · Sectorielle · Régions · Diaspora'],
            ['safety@govgenz.org', 50, 'fn.safety.name', 'SÉCURITÉ', 'fn.safety.sub', 'Préventive &amp; curative · Legal · Tech · Field'],
            ['communication@govgenz.org', 100, 'fn.com.name', 'COMMUNICATION', 'fn.com.sub', 'Stratégie · Contenus · Réseaux · Vulgarisation'],
            ['partnerships@govgenz.org', 150, 'fn.part.name', 'PARTENARIATS', 'fn.part.sub', 'Stratégiques · National &amp; international'],
            ['recruitment@govgenz.org', 200, 'fn.rh.name', 'RESSOURCES HUMAINES', 'fn.rh.sub', 'Recrutement · Onboarding · Formation'],
            ['projects@govgenz.org', 250, 'fn.pmo.name', 'PROJECT MANAGEMENT', 'fn.pmo.sub', 'PMO · Suivi · Impact · KPI'],
            ['finance@govgenz.org', 300, 'fn.fin.name', 'FINANCES', 'fn.fin.sub', 'Comptabilité · Levée · Trésorerie'],
        ];

        $fnHtml = '';
        foreach ($fns as [$mail, $delay, $nk, $nf, $sk, $sf]) {
            $fnHtml .= '<a href="mailto:' . esc($mail, 'attr') . '" class="fn-card reveal" data-delay="' . (int) $delay . '">';
            $fnHtml .= '<div class="fn-card__name" data-i18n="' . esc($nk, 'attr') . '">' . esc($nf) . '</div>';
            $fnHtml .= '<div class="fn-card__sub" data-i18n="' . esc($sk, 'attr') . '">' . $sf . '</div>';
            $fnHtml .= '<div class="fn-card__mail">' . esc($mail) . '</div>';
            $fnHtml .= '</a>';
        }

        return <<<HTML
<section class="section section--structure" id="structure-content" aria-labelledby="structure-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline" data-i18n="structure.overline">NOTRE STRUCTURE</div>
            <h1 class="section__title" id="structure-heading" data-i18n="structure.title">Une organisation transparente</h1>
            <p class="section__lead" data-i18n="structure.lead">
                Un noyau exécutif central, sept fonctions transversales, quatorze équipes sectorielles. Chaque fonction est contactable directement.
            </p>
        </div>
        <div class="hub">
            <div class="hub__core">
                <div class="hub__label" data-i18n="structure.noyau.label">NOYAU EXÉCUTIF CENTRAL</div>
                <div class="hub__sub" data-i18n="structure.noyau.sub">Coordination · Sécurité · Vision · Décision</div>
                <a href="mailto:contact@govgenz.org" class="hub__mail">contact@govgenz.org</a>
            </div>
            <div class="hub__grid">
                {$fnHtml}
            </div>
        </div>
    </div>
</section>
HTML;
    }

    public static function secteurs(): string
    {
        $sectorRows = [
            ['LEGAL', 'sect.legal', 'Justice · Gouvernance · Anti-corruption', 'legal@govgenz.org'],
            ['ECONOMY', 'sect.economy', 'Finances publiques · Commerce · Emploi', 'economy@govgenz.org'],
            ['FOOD', 'sect.food', 'Agriculture · Pêche · Souveraineté alimentaire', 'food@govgenz.org'],
            ['ENERGY', 'sect.energy', 'Énergies renouvelables · Solaire · Éolien', 'energy@govgenz.org'],
            ['WATER', 'sect.water', 'Eau &amp; assainissement · Accès · Qualité', 'water@govgenz.org'],
            ['EDUCATION', 'sect.education', 'Formation · Recherche · Innovation', 'education@govgenz.org'],
            ['HEALTH', 'sect.health', 'Santé · Nutrition · Protection sociale', 'health@govgenz.org'],
            ['INFRASTRUCTURE', 'sect.infra', 'Transport · Désenclavement', 'infrastructure@govgenz.org'],
            ['DIGITAL', 'sect.digital', 'Numérique · Données · IA', 'digital@govgenz.org'],
            ['TERRITORIES', 'sect.terr', 'Décentralisation · Foncier · Logement', 'territories@govgenz.org'],
            ['ENVIRONMENT', 'sect.env', 'Climat · Ressources naturelles', 'environment@govgenz.org'],
            ['MINES', 'sect.mines', 'Ressources minières · Traçabilité', 'mines@govgenz.org'],
            ['SECURITY', 'sect.security', 'Sécurité civile · Gestion crise', 'security@govgenz.org'],
            ['CITIZEN', 'sect.citizen', 'Jeunesse · Culture · Diaspora', 'citizen@govgenz.org'],
        ];

        $cards = '';
        foreach ($sectorRows as $idx => $sr) {
            [$code, $i18nKey, $labelFr, $email] = $sr;
            $delay = $idx * 40;
            $cards .= '<a href="mailto:' . esc($email, 'attr') . '" class="tile reveal" data-delay="' . $delay . '">';
            $cards .= '<div class="tile__name">' . esc($code) . '</div>';
            $cards .= '<div class="tile__sub" data-i18n="' . esc($i18nKey, 'attr') . '">' . $labelFr . '</div>';
            $cards .= '<div class="tile__mail">' . esc($email) . '</div>';
            $cards .= '</a>';
        }

        return <<<HTML
<section class="section section--secteurs" id="secteurs-content" aria-labelledby="secteurs-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline" data-i18n="sect.overline">14 ÉQUIPES SECTORIELLES</div>
            <h1 class="section__title" id="secteurs-heading" data-i18n="sect.title">Bâtir secteur par secteur</h1>
            <p class="section__lead" data-i18n="sect.lead">
                Quatorze domaines d'action couvrant l'ensemble des enjeux du Madagascar de demain. Contactez directement l'équipe concernée.
            </p>
        </div>
        <div class="tile-grid">
            {$cards}
        </div>
    </div>
</section>
HTML;
    }

    public static function etude(): string
    {
        $studyMail = 'mailto:contact@govgenz.org?subject=' . rawurlencode('Demande de l\'étude jeunesse');

        return <<<HTML
<section class="section section--etude" id="etude-content" aria-labelledby="etude-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline" data-i18n="etude.overline">ÉTUDE JEUNESSE 2026</div>
            <h1 class="section__title" id="etude-heading" data-i18n="etude.title">Les chiffres qui nous portent</h1>
            <p class="section__lead" data-i18n="etude.lead">
                Une base chiffrée pour comprendre le poids démographique de la jeunesse malgache et les leviers d'action à activer.
            </p>
        </div>
        <div class="stats">
            <div class="stat reveal" data-delay="0">
                <div class="stat__num"><span data-count="72.6">72,6</span><span class="stat__suffix">%</span></div>
                <div class="stat__label" data-i18n="etude.s1">de la population a 0–30 ans</div>
            </div>
            <div class="stat reveal" data-delay="100">
                <div class="stat__num"><span data-count="75.2">75,2</span><span class="stat__suffix">%</span></div>
                <div class="stat__label" data-i18n="etude.s2">de pauvreté nationale en 2022</div>
            </div>
            <div class="stat reveal" data-delay="200">
                <div class="stat__num"><span data-count="47">47</span><span class="stat__suffix">%</span></div>
                <div class="stat__label" data-i18n="etude.s3">des 5–17 ans concernés par le travail des enfants</div>
            </div>
            <div class="stat reveal" data-delay="300">
                <div class="stat__num"><span data-count="13">13</span><span class="stat__suffix">%</span></div>
                <div class="stat__label" data-i18n="etude.s4">de fréquentation au secondaire second cycle</div>
            </div>
        </div>
        <div class="section__cta">
            <p class="section__cta-lead" data-i18n="etude.cta.text">L'étude complète est disponible. Elle couvre les 24 régions, l'éducation, la santé, l'emploi, la diaspora et les leviers d'action.</p>
            <a href="{$studyMail}" class="btn btn--primary" data-i18n="etude.cta.btn">Demander l'étude complète</a>
        </div>
    </div>
</section>
HTML;
    }

    public static function contact(): string
    {
        $join = site_url('join');

        return <<<HTML
<section class="section section--contact" id="contact-content" aria-labelledby="contact-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline" data-i18n="contact.overline">REJOINDRE LE MOUVEMENT</div>
            <h1 class="section__title" id="contact-heading" data-i18n="contact.title">Notre avenir se défend maintenant</h1>
            <p class="section__lead" data-i18n="contact.lead">
                Que vous soyez jeune, dans la diaspora, sympathisant, expert, journaliste ou partenaire, il existe une porte d'entrée pour vous.
            </p>
        </div>
        <div class="contact-card">
            <div class="contact-card__inner">
                <div class="contact-grid">
                    <a href="mailto:contact@govgenz.org" class="contact-block">
                        <div class="contact-block__label" data-i18n="contact.b1.label">CONTACT GÉNÉRAL</div>
                        <div class="contact-block__mail">contact@govgenz.org</div>
                        <div class="contact-block__sub" data-i18n="contact.b1.sub">Toutes questions, premier contact</div>
                    </a>
                    <a href="{$join}" class="contact-block">
                        <div class="contact-block__label" data-i18n="contact.b2.label">REJOINDRE</div>
                        <div class="contact-block__mail" data-i18n="contact.b2.mail">Formulaire en ligne</div>
                        <div class="contact-block__sub" data-i18n="contact.b2.sub">Devenir membre actif, par secteur ou région</div>
                    </a>
                    <a href="mailto:partnerships@govgenz.org" class="contact-block">
                        <div class="contact-block__label" data-i18n="contact.b3.label">PARTENARIAT</div>
                        <div class="contact-block__mail">partnerships@govgenz.org</div>
                        <div class="contact-block__sub" data-i18n="contact.b3.sub">Organisations, PTF, structures alliées</div>
                    </a>
                    <a href="mailto:communication@govgenz.org?subject=Demande%20presse" class="contact-block">
                        <div class="contact-block__label" data-i18n="contact.b4.label">PRESSE</div>
                        <div class="contact-block__mail">communication@govgenz.org</div>
                        <div class="contact-block__sub" data-i18n="contact.b4.sub">Médias nationaux et internationaux</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
HTML;
    }
}
