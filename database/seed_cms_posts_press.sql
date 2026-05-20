-- Seed communiqués de presse (cms_posts) — Gov Gen Z Madagascar
-- Paires FR + EN (translation_group = clé commune par article).
--
-- Prérequis : table cms_posts avec colonnes locale, translation_group (migration 2026-05-07).
-- Usage : mysql -u USER -p BASE < database/seed_cms_posts_press.sql
--
-- Supprime uniquement les slugs listés ci-dessous, puis réinsère (idempotent sur ce jeu de démo).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM cms_posts
WHERE slug IN (
  'lancement-mouvement-2026',
  'forum-jeunesse-tananarivo',
  'appel-volontaires-secteurs',
  'movement-launch-2026',
  'youth-forum-antananarivo',
  'volunteer-call-sector-teams'
);

-- ---------------------------------------------------------------------------
-- 1. Lancement du mouvement
-- ---------------------------------------------------------------------------
INSERT INTO cms_posts (
  slug, locale, translation_group, title, excerpt, body_html, status,
  published_at, meta_title, meta_description, created_at, updated_at
) VALUES (
  'lancement-mouvement-2026',
  'fr',
  'lancement-mouvement-2026',
  'Lancement officiel de Gov Gen Z Madagascar',
  'Le mouvement présente sa feuille de route et son site public pour mobiliser la jeunesse autour de quatorze secteurs d’action.',
  '<p><strong>Antananarivo, 15 mars 2026</strong> — Gov Gen Z Madagascar annonce aujourd’hui le lancement de sa plateforme numérique et de sa feuille de route sectorielle. L’initiative vise à structurer l’engagement citoyen des jeunes autour de projets concrets, avec une transparence renforcée sur les financements et les résultats.</p>
<h2>Une approche par secteurs</h2>
<p>Quatorze équipes thématiques couvrent les enjeux prioritaires du pays : éducation, santé, économie, environnement, justice, gouvernance et autres domaines essentiels au développement national.</p>
<ul>
<li>Publication des premiers projets pilotes sur le portail <em>Projets</em></li>
<li>Formulaire <em>Rejoindre</em> ouvert aux candidatures volontaires</li>
<li>Espace presse dédié aux communiqués et contacts médias</li>
</ul>
<h2>Prochaines étapes</h2>
<p>Les équipes sectorielles tiendront des points presse régionaux au second trimestre 2026. Les partenaires institutionnels et la société civile sont invités à prendre contact via le formulaire en ligne.</p>
<p><em>Contact presse :</em> <a href="mailto:apps@govgenz.org">apps@govgenz.org</a></p>',
  'published',
  '2026-03-15 09:00:00',
  'Lancement officiel — Gov Gen Z Madagascar',
  'Communiqué de presse : lancement de Gov Gen Z Madagascar, feuille de route et site public.',
  '2026-03-15 09:00:00',
  '2026-03-15 09:00:00'
);

INSERT INTO cms_posts (
  slug, locale, translation_group, title, excerpt, body_html, status,
  published_at, meta_title, meta_description, created_at, updated_at
) VALUES (
  'movement-launch-2026',
  'en',
  'lancement-mouvement-2026',
  'Official launch of Gov Gen Z Madagascar',
  'The movement unveils its public website and sector roadmap to mobilize youth across fourteen areas of action.',
  '<p><strong>Antananarivo, 15 March 2026</strong> — Gov Gen Z Madagascar today announces the launch of its digital platform and sector roadmap. The initiative aims to structure youth civic engagement around concrete projects, with stronger transparency on funding and outcomes.</p>
<h2>A sector-based approach</h2>
<p>Fourteen thematic teams address the country''s priority issues: education, health, economy, environment, justice, governance, and other areas essential to national development.</p>
<ul>
<li>First pilot projects published on the <em>Projects</em> hub</li>
<li><em>Join</em> form open for volunteer applications</li>
<li>Dedicated press area for releases and media contacts</li>
</ul>
<h2>Next steps</h2>
<p>Sector teams will hold regional press briefings in Q2 2026. Institutional partners and civil society are invited to get in touch via the online form.</p>
<p><em>Press contact:</em> <a href="mailto:apps@govgenz.org">apps@govgenz.org</a></p>',
  'published',
  '2026-03-15 10:00:00',
  'Official launch — Gov Gen Z Madagascar',
  'Press release: launch of Gov Gen Z Madagascar, roadmap and public website.',
  '2026-03-15 10:00:00',
  '2026-03-15 10:00:00'
);

-- ---------------------------------------------------------------------------
-- 2. Forum jeunesse
-- ---------------------------------------------------------------------------
INSERT INTO cms_posts (
  slug, locale, translation_group, title, excerpt, body_html, status,
  published_at, meta_title, meta_description, created_at, updated_at
) VALUES (
  'forum-jeunesse-tananarivo',
  'fr',
  'forum-jeunesse-tananarivo',
  'Forum national de la jeunesse : plus de 400 participants à Antananarivo',
  'Retour sur une journée d’échanges entre jeunes leaders, acteurs publics et partenaires autour des priorités 2026–2030.',
  '<p><strong>Antananarivo, 2 mai 2026</strong> — Le Forum national de la jeunesse organisé par Gov Gen Z Madagascar a réuni plus de 400 participants à l’Université d’Antananarivo. Tables rondes, ateliers sectoriels et sessions de co-construction ont permis d’identifier des actions prioritaires pour les dix-huit prochains mois.</p>
<h2>Messages clés</h2>
<ul>
<li>Renforcer l’accès à l’éducation numérique en milieu rural</li>
<li>Accélérer les projets eau potable et énergie solaire communautaire</li>
<li>Ouvrir davantage de places de volontariat structuré par secteur</li>
</ul>
<h2>Documents</h2>
<p>Le compte rendu synthétique sera publié sur le site sous quinze jours. Les médias peuvent demander les photos officielles et les citations des intervenants à l’adresse ci-dessous.</p>
<p><em>Contact presse :</em> <a href="mailto:apps@govgenz.org">apps@govgenz.org</a></p>',
  'published',
  '2026-05-02 14:30:00',
  'Forum jeunesse Antananarivo — Gov Gen Z',
  'Communiqué : forum national de la jeunesse, priorités 2026 et engagement sectoriel.',
  '2026-05-02 14:30:00',
  '2026-05-02 14:30:00'
);

INSERT INTO cms_posts (
  slug, locale, translation_group, title, excerpt, body_html, status,
  published_at, meta_title, meta_description, created_at, updated_at
) VALUES (
  'youth-forum-antananarivo',
  'en',
  'forum-jeunesse-tananarivo',
  'National youth forum: 400+ participants in Antananarivo',
  'A day of dialogue between young leaders, public actors, and partners on 2026–2030 priorities.',
  '<p><strong>Antananarivo, 2 May 2026</strong> — The National Youth Forum hosted by Gov Gen Z Madagascar brought together more than 400 participants at the University of Antananarivo. Sector workshops and co-design sessions helped identify priority actions for the next eighteen months.</p>
<h2>Key messages</h2>
<ul>
<li>Strengthen rural digital education access</li>
<li>Accelerate community water and solar energy projects</li>
<li>Expand structured volunteer pathways by sector</li>
</ul>
<h2>Materials</h2>
<p>A summary report will be published on the website within two weeks. Media may request official photos and speaker quotes at the address below.</p>
<p><em>Press contact:</em> <a href="mailto:apps@govgenz.org">apps@govgenz.org</a></p>',
  'published',
  '2026-05-02 15:00:00',
  'Youth forum Antananarivo — Gov Gen Z',
  'Press release: national youth forum and 2026 sector priorities.',
  '2026-05-02 15:00:00',
  '2026-05-02 15:00:00'
);

-- ---------------------------------------------------------------------------
-- 3. Appel volontaires
-- ---------------------------------------------------------------------------
INSERT INTO cms_posts (
  slug, locale, translation_group, title, excerpt, body_html, status,
  published_at, meta_title, meta_description, created_at, updated_at
) VALUES (
  'appel-volontaires-secteurs',
  'fr',
  'appel-volontaires-secteurs',
  'Appel à volontaires : rejoignez une équipe sectorielle',
  'Gov Gen Z ouvre les candidatures pour renforcer les équipes terrain et la coordination des projets.',
  '<p><strong>En ligne, 18 mai 2026</strong> — Gov Gen Z Madagascar lance un appel national aux jeunes souhaitant s’engager dans une équipe sectorielle. Les profils recherchés couvrent la coordination de projet, la communication, l’expertise technique et le lien avec les communautés locales.</p>
<h2>Comment candidater</h2>
<ol>
<li>Consulter la page <a href="/secteurs">Secteurs</a> pour choisir un ou plusieurs domaines</li>
<li>Remplir le formulaire <a href="/join">Rejoindre</a> (coordonnées et message de motivation)</li>
<li>Attendre le retour de l’équipe sous dix jours ouvrés</li>
</ol>
<h2>Engagement attendu</h2>
<p>Les volontaires retenus participent à des réunions mensuelles, au suivi des indicateurs de leurs projets et, le cas échéant, à des missions courtes sur le terrain. Aucune rémunération n’est promise à ce stade ; l’engagement est citoyen et formatif.</p>
<p><em>Contact presse :</em> <a href="mailto:apps@govgenz.org">apps@govgenz.org</a></p>',
  'published',
  '2026-05-18 08:00:00',
  'Appel volontaires — Gov Gen Z Madagascar',
  'Communiqué : recrutement de volontaires pour les équipes sectorielles Gov Gen Z.',
  '2026-05-18 08:00:00',
  '2026-05-18 08:00:00'
);

INSERT INTO cms_posts (
  slug, locale, translation_group, title, excerpt, body_html, status,
  published_at, meta_title, meta_description, created_at, updated_at
) VALUES (
  'volunteer-call-sector-teams',
  'en',
  'appel-volontaires-secteurs',
  'Call for volunteers: join a sector team',
  'Gov Gen Z opens applications to strengthen field teams and project coordination.',
  '<p><strong>Online, 18 May 2026</strong> — Gov Gen Z Madagascar launches a national call for young people who wish to join a sector team. Profiles sought include project coordination, communications, technical expertise, and community outreach.</p>
<h2>How to apply</h2>
<ol>
<li>Visit the <a href="/en/sectors">Sectors</a> page to choose one or more areas</li>
<li>Complete the <a href="/en/join">Join</a> form (contact details and motivation)</li>
<li>Expect a response from the team within ten business days</li>
</ol>
<h2>Expected commitment</h2>
<p>Selected volunteers take part in monthly meetings, track project indicators, and may join short field missions. This is a civic, learning-oriented engagement; no salary is offered at this stage.</p>
<p><em>Press contact:</em> <a href="mailto:apps@govgenz.org">apps@govgenz.org</a></p>',
  'published',
  '2026-05-18 09:00:00',
  'Volunteer call — Gov Gen Z Madagascar',
  'Press release: volunteer recruitment for Gov Gen Z sector teams.',
  '2026-05-18 09:00:00',
  '2026-05-18 09:00:00'
);

SET FOREIGN_KEY_CHECKS = 1;
