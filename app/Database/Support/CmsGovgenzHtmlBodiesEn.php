<?php

declare(strict_types=1);

namespace App\Database\Support;

/**
 * English HTML for public CMS pages (/en/…), aligned with {@see CmsGovgenzHtmlBodies}.
 */
final class CmsGovgenzHtmlBodiesEn
{
    public static function home(): string
    {
        helper('url');
        $qui     = site_url('en/who-we-are');
        $contact = site_url('en/contact');
        $join    = site_url('en/join');

        return <<<HTML
<section class="ggz-page-hero ggz-home-section" id="accueil" aria-labelledby="hero-heading">
    <span class="ggz-eyebrow">Paikady Taninjanaka programme</span>
    <h1 id="hero-heading">GovGenZ Madagascar</h1>
    <p class="ggz-lead">A structured movement to build a dignified, peaceful and sustainable future — dignity and serenity for the people, and a better future for youth and generations to come.</p>
    <div class="ggz-actions">
        <a class="btn btn-primary" href="{$qui}">Discover the movement</a>
        <a class="btn btn-secondary" href="{$contact}">Contact us</a>
        <a class="btn btn-secondary" href="{$join}">Join us</a>
    </div>
    <ul class="ggz-trust-list">
        <li>Executive core &amp; coordination</li>
        <li>14 sector teams</li>
        <li>Measurable impact</li>
    </ul>
</section>
<p class="muted">Section details below can be edited in <strong>Admin → Pages</strong> (slugs: <code>who-we-are</code>, <code>our-dna</code>, etc.).</p>
HTML;
    }

    public static function quiSommesNous(): string
    {
        $rows = [
            [
                'delay' => 0,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 7a4 4 0 100 8 4 4 0 000-8zM4 21v-1a6 6 0 016-6h4a6 6 0 016 6v1"/></svg>',
                'countAttr' => '12.44',
                'countText' => '12.44',
                'infinity'  => false,
                'unit'      => 'M',
                'title'     => 'Children',
                'sub'       => '0–17 · 48.5%',
                'desc'      => 'The country\'s future starts today',
            ],
            [
                'delay' => 100,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM3 21v-2a4 4 0 014-4h10a4 4 0 014 4v2"/></svg>',
                'countAttr' => '8.68',
                'countText' => '8.68',
                'infinity'  => false,
                'unit'      => 'M',
                'title'     => 'Youth',
                'sub'       => '14–30 · 33.8%',
                'desc'      => 'The heart of mobilisation and building together',
            ],
            [
                'delay' => 200,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20M5 9c2 0 4 2 4 4M19 9c-2 0-4 2-4 4M12 12c-2-2-4-2-6 0M12 12c2-2 4-2 6 0"/></svg>',
                'infinity' => true,
                'unit'     => '',
                'title'    => 'Next generations',
                'sub'      => 'Future generations',
                'desc'     => 'Building a legacy that protects them',
            ],
            [
                'delay' => 300,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg>',
                'infinity' => true,
                'unit'     => '',
                'title'    => 'Diaspora',
                'sub'      => 'Malagasy worldwide',
                'desc'     => 'Skills, mentoring and international advocacy',
            ],
            [
                'delay' => 400,
                'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>',
                'infinity' => true,
                'unit'     => '',
                'title'    => 'Supporters',
                'sub'      => 'Everyone who stands with us',
                'desc'     => 'All constructive goodwill',
            ],
        ];

        $cards = '';
        foreach ($rows as $r) {
            $delay = (int) $r['delay'];
            $cards .= '<div class="cercle reveal" data-delay="' . $delay . '">';
            $cards .= '<div class="cercle__icon">' . $r['icon'] . '</div>';
            if (! empty($r['infinity'])) {
                $cards .= '<div class="cercle__number cercle__number--text">∞</div>';
            } else {
                $ca = esc((string) ($r['countAttr'] ?? ''), 'attr');
                $ct = esc((string) ($r['countText'] ?? ''));
                $cards .= '<div class="cercle__number" data-count="' . $ca . '">' . $ct . '</div>';
                $cards .= '<div class="cercle__unit">' . esc((string) ($r['unit'] ?? '')) . '</div>';
            }
            $cards .= '<h3 class="cercle__title">' . esc($r['title']) . '</h3>';
            $cards .= '<p class="cercle__sub">' . esc($r['sub']) . '</p>';
            $cards .= '<p class="cercle__desc">' . esc($r['desc']) . '</p>';
            $cards .= '</div>';
        }

        return <<<HTML
<section class="section section--qui" id="qui-content" aria-labelledby="qs-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline">WHO WE ARE</div>
            <h1 class="section__title" id="qs-heading">For them, with you</h1>
            <p class="section__lead">
                We stand for five concentric circles: those building today, those who will inherit tomorrow, and everyone supporting us across the world.
            </p>
        </div>
        <div class="cercles">
            {$cards}
        </div>
        <div class="section__source"><span>Source · Gov Gen Z Madagascar Youth Study 2026</span></div>
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
                'over'  => 'FOR WHOM',
                'title' => 'Our purpose',
                'lines' => [
                    '8.68M young people (14–30)',
                    '12.44M children (0–17)',
                    'The global Malagasy diaspora',
                    'Future generations',
                ],
            ],
            [
                'delay' => 100,
                'num'   => '02',
                'cls'   => 'adn-card--2',
                'over'  => 'WHAT GUIDES US',
                'title' => 'Our values',
                'lines' => [
                    'Integrity · Ethics',
                    'Mutual aid · Harmony',
                    'Speed · Effectiveness',
                    'Serving the mission',
                ],
            ],
            [
                'delay' => 200,
                'num'   => '03',
                'cls'   => 'adn-card--3',
                'over'  => 'HOW',
                'title' => 'Our method',
                'lines' => [
                    'Collective intelligence',
                    'Citizen co-building',
                    '15% think · 85% act',
                    'Measurable, useful impact',
                ],
            ],
            [
                'delay' => 300,
                'num'   => '04',
                'cls'   => 'adn-card--4',
                'over'  => 'WHAT FOR',
                'title' => 'Our aim',
                'lines' => [
                    'Dignity &amp; serenity',
                    'National sovereignty',
                    'Systems that serve the people',
                    'A better, lasting future',
                ],
            ],
        ];

        $blocks = '';
        foreach ($pillars as $p) {
            $lis = '';
            foreach ($p['lines'] as $line) {
                $lis .= '<li>' . $line . '</li>';
            }
            $blocks .= '<article class="adn-card ' . esc($p['cls'], 'attr') . ' reveal" data-delay="' . (int) $p['delay'] . '">';
            $blocks .= '<div class="adn-card__num">' . esc($p['num']) . '</div>';
            $blocks .= '<div class="adn-card__overline">' . esc($p['over']) . '</div>';
            $blocks .= '<h3 class="adn-card__title">' . esc($p['title']) . '</h3>';
            $blocks .= '<ul class="adn-card__list">' . $lis . '</ul>';
            $blocks .= '</article>';
        }

        return <<<HTML
<section class="section section--adn" id="adn-content" aria-labelledby="adn-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline">GOV GEN Z MADAGASCAR DNA</div>
            <h1 class="section__title" id="adn-heading">What drives us</h1>
            <p class="section__lead">
                Four pillars that define who we are, what we want and how we move forward.
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
            ['coordination@govgenz.org', 0, 'COORDINATION', 'Executive · Sector · Regions · Diaspora'],
            ['safety@govgenz.org', 50, 'SECURITY', 'Preventive &amp; corrective · Legal · Tech · Field'],
            ['communication@govgenz.org', 100, 'COMMUNICATION', 'Strategy · Content · Networks · Outreach'],
            ['partnerships@govgenz.org', 150, 'PARTNERSHIPS', 'Strategic · National &amp; international'],
            ['recruitment@govgenz.org', 200, 'HUMAN RESOURCES', 'Recruitment · Onboarding · Training'],
            ['projects@govgenz.org', 250, 'PROJECT MANAGEMENT', 'PMO · Tracking · Impact · KPIs'],
            ['finance@govgenz.org', 300, 'FINANCE', 'Accounting · Fundraising · Treasury'],
        ];

        $fnHtml = '';
        foreach ($fns as [$mail, $delay, $nf, $sf]) {
            $fnHtml .= '<a href="mailto:' . esc($mail, 'attr') . '" class="fn-card reveal" data-delay="' . (int) $delay . '">';
            $fnHtml .= '<div class="fn-card__name">' . esc($nf) . '</div>';
            $fnHtml .= '<div class="fn-card__sub">' . $sf . '</div>';
            $fnHtml .= '<div class="fn-card__mail">' . esc($mail) . '</div>';
            $fnHtml .= '</a>';
        }

        return <<<HTML
<section class="section section--structure" id="structure-content" aria-labelledby="structure-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline">OUR STRUCTURE</div>
            <h1 class="section__title" id="structure-heading">A transparent organisation</h1>
            <p class="section__lead">
                A central executive core, seven cross-cutting functions and fourteen sector teams. Each function can be reached directly.
            </p>
        </div>
        <div class="hub">
            <div class="hub__core">
                <div class="hub__label">CENTRAL EXECUTIVE CORE</div>
                <div class="hub__sub">Coordination · Security · Vision · Decisions</div>
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
            ['LEGAL', 'Justice · Governance · Anti-corruption', 'legal@govgenz.org'],
            ['ECONOMY', 'Public finance · Trade · Employment', 'economy@govgenz.org'],
            ['FOOD', 'Agriculture · Fisheries · Food sovereignty', 'food@govgenz.org'],
            ['ENERGY', 'Renewable energy · Solar · Wind', 'energy@govgenz.org'],
            ['WATER', 'Water &amp; sanitation · Access · Quality', 'water@govgenz.org'],
            ['EDUCATION', 'Training · Research · Innovation', 'education@govgenz.org'],
            ['HEALTH', 'Health · Nutrition · Social protection', 'health@govgenz.org'],
            ['INFRASTRUCTURE', 'Transport · Connectivity', 'infrastructure@govgenz.org'],
            ['DIGITAL', 'Digital · Data · AI', 'digital@govgenz.org'],
            ['TERRITORIES', 'Decentralisation · Land · Housing', 'territories@govgenz.org'],
            ['ENVIRONMENT', 'Climate · Natural resources', 'environment@govgenz.org'],
            ['MINES', 'Mineral resources · Traceability', 'mines@govgenz.org'],
            ['SECURITY', 'Civil security · Crisis management', 'security@govgenz.org'],
            ['CITIZEN', 'Youth · Culture · Diaspora', 'citizen@govgenz.org'],
        ];

        $cards = '';
        foreach ($sectorRows as $idx => $sr) {
            [$code, $label, $email] = $sr;
            $delay = $idx * 40;
            $cards .= '<a href="mailto:' . esc($email, 'attr') . '" class="tile reveal" data-delay="' . $delay . '">';
            $cards .= '<div class="tile__name">' . esc($code) . '</div>';
            $cards .= '<div class="tile__sub">' . $label . '</div>';
            $cards .= '<div class="tile__mail">' . esc($email) . '</div>';
            $cards .= '</a>';
        }

        return <<<HTML
<section class="section section--secteurs" id="secteurs-content" aria-labelledby="secteurs-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline">14 SECTOR TEAMS</div>
            <h1 class="section__title" id="secteurs-heading">Building sector by sector</h1>
            <p class="section__lead">
                Fourteen fields of action covering the challenges of tomorrow’s Madagascar. Contact the relevant team directly.
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
        $studyMail = 'mailto:contact@govgenz.org?subject=' . rawurlencode('Request: Gov Gen Z youth study');

        return <<<HTML
<section class="section section--etude" id="etude-content" aria-labelledby="etude-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline">YOUTH STUDY 2026</div>
            <h1 class="section__title" id="etude-heading">The numbers that drive us</h1>
            <p class="section__lead">
                Data to understand the demographic weight of Malagasy youth and the levers we can activate together.
            </p>
        </div>
        <div class="stats">
            <div class="stat reveal" data-delay="0">
                <div class="stat__num"><span data-count="72.6">72.6</span><span class="stat__suffix">%</span></div>
                <div class="stat__label">of the population is aged 0–30</div>
            </div>
            <div class="stat reveal" data-delay="100">
                <div class="stat__num"><span data-count="75.2">75.2</span><span class="stat__suffix">%</span></div>
                <div class="stat__label">national poverty rate (2022)</div>
            </div>
            <div class="stat reveal" data-delay="200">
                <div class="stat__num"><span data-count="47">47</span><span class="stat__suffix">%</span></div>
                <div class="stat__label">of children 5–17 affected by child labour</div>
            </div>
            <div class="stat reveal" data-delay="300">
                <div class="stat__num"><span data-count="13">13</span><span class="stat__suffix">%</span></div>
                <div class="stat__label">net attendance in upper secondary</div>
            </div>
        </div>
        <div class="section__cta">
            <p class="section__cta-lead">The full study is available. It covers all 22 regions, education, health, employment, the diaspora and action levers.</p>
            <a href="{$studyMail}" class="btn btn--primary">Request the full study</a>
        </div>
    </div>
</section>
HTML;
    }

    public static function contact(): string
    {
        helper('url');
        $join = site_url('en/join');

        return <<<HTML
<section class="section section--contact" id="contact-content" aria-labelledby="contact-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline">JOIN THE MOVEMENT</div>
            <h1 class="section__title" id="contact-heading">Our future is shaped now</h1>
            <p class="section__lead">
                Whether you are young, in the diaspora, an ally, expert, journalist or partner — there is an entry point for you.
            </p>
        </div>
        <div class="contact-card">
            <div class="contact-card__inner">
                <div class="contact-grid">
                    <a href="mailto:contact@govgenz.org" class="contact-block">
                        <div class="contact-block__label">GENERAL CONTACT</div>
                        <div class="contact-block__mail">contact@govgenz.org</div>
                        <div class="contact-block__sub">Any question, first touchpoint</div>
                    </a>
                    <a href="{$join}" class="contact-block">
                        <div class="contact-block__label">JOIN US</div>
                        <div class="contact-block__mail">Online form</div>
                        <div class="contact-block__sub">Become an active member by sector or region</div>
                    </a>
                    <a href="mailto:partnerships@govgenz.org" class="contact-block">
                        <div class="contact-block__label">PARTNERSHIPS</div>
                        <div class="contact-block__mail">partnerships@govgenz.org</div>
                        <div class="contact-block__sub">Organisations, donors and allied institutions</div>
                    </a>
                    <a href="mailto:communication@govgenz.org?subject=Press%20inquiry" class="contact-block">
                        <div class="contact-block__label">PRESS</div>
                        <div class="contact-block__mail">communication@govgenz.org</div>
                        <div class="contact-block__sub">National and international media</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
HTML;
    }
}
