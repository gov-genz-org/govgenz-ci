<?php

declare(strict_types=1);

/**
 * Génère database/seed_project_projects_from_static.sql depuis :
 * - FR : site_govgenz/projects-govgenz/projects/*.html
 * - EN : database/project_seed_en/*.html (même nom de fichier)
 * Usage : php database/build_seed_project_projects_static.php
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use App\Libraries\ProjectBodyHtmlToBlocks;

$staticDir = $root . '/../site_govgenz/projects-govgenz/projects';
$enHtmlDir  = $root . '/database/project_seed_en';
$outFile    = $root . '/database/seed_project_projects_from_static.sql';

$sectorMap = [
    'EDUCATION' => 'education',
    'DIGITAL' => 'digital',
    'FOOD' => 'food',
    'ECONOMY' => 'economy',
    'WATER' => 'water',
    'HEALTH' => 'health',
    'ENERGY' => 'energy',
    'ENVIRONMENT' => 'environment',
    'LEGAL' => 'legal',
    'CITIZEN' => 'citizen',
    'TERRITORIES' => 'territories',
    'INFRASTRUCTURE' => 'infrastructure',
    'MINES' => 'mines',
    'SECURITY' => 'security',
];

$cards = [
    [
        'slug' => 'connectez-ecoles',
        'file' => 'connectez-ecoles.html',
        'sectors_upper' => ['EDUCATION', 'DIGITAL'],
        'status' => 'actif',
        'volunteers' => 127,
        'budget_display' => '850 M Ar',
        'geography' => '10 régions',
        'excerpt' => 'Connectivité internet et énergie solaire pour 500 écoles rurales, avec formation des enseignants au numérique éducatif.',
        'en_excerpt' => 'Internet connectivity and solar power for 500 rural schools, with teacher training in digital education.',
        'en_geography' => '10 regions',
        'launched_at' => '2026-03-01',
        'duration_months' => 30,
        'progress_percent' => 38,
    ],
    [
        'slug' => 'agrotech-jeunesse',
        'file' => 'agrotech-jeunesse.html',
        'sectors_upper' => ['FOOD', 'ECONOMY'],
        'status' => 'actif',
        'volunteers' => 89,
        'budget_display' => '420 M Ar',
        'geography' => 'Vakinankaratra',
        'excerpt' => 'Former 1 000 jeunes agriculteurs avec technologies durables, semences améliorées et accès direct aux marchés via une plateforme digitale.',
        'en_excerpt' => 'Train 1,000 young farmers with sustainable techniques, improved seed, and direct market access through a digital platform.',
        'en_geography' => 'Vakinankaratra',
        'launched_at' => '2026-01-01',
        'duration_months' => 24,
        'progress_percent' => 55,
    ],
    [
        'slug' => 'eau-potable-2026',
        'file' => 'eau-potable-2026.html',
        'sectors_upper' => ['WATER', 'HEALTH'],
        'status' => 'candidat',
        'volunteers' => 45,
        'budget_display' => '280 M Ar',
        'geography' => 'Hauts Plateaux',
        'excerpt' => 'Construction de 50 points d\'eau potable dans les Hauts Plateaux avec formation locale en maintenance et gouvernance communautaire.',
        'en_excerpt' => 'Build 50 safe water points in the Central Highlands with local maintenance training and community governance.',
        'en_geography' => 'Central Highlands',
        'launched_at' => '2026-04-01',
        'duration_months' => 18,
        'progress_percent' => 0,
    ],
    [
        'slug' => 'energie-solaire',
        'file' => 'energie-solaire.html',
        'sectors_upper' => ['ENERGY', 'ENVIRONMENT'],
        'status' => 'actif',
        'volunteers' => 156,
        'budget_display' => '920 M Ar',
        'geography' => 'Sud & DIANA',
        'excerpt' => '200 mini-grids solaires en zones isolées pour créer des emplois locaux et réduire les émissions de CO₂ de manière mesurable.',
        'en_excerpt' => '200 solar mini-grids in isolated areas to create local jobs and reduce CO₂ emissions in a measurable way.',
        'en_geography' => 'South & DIANA',
        'launched_at' => '2026-02-01',
        'duration_months' => 36,
        'progress_percent' => 22,
    ],
    [
        'slug' => 'acces-justice',
        'file' => 'acces-justice.html',
        'sectors_upper' => ['LEGAL', 'CITIZEN'],
        'status' => 'validation',
        'volunteers' => 67,
        'budget_display' => '195 M Ar',
        'geography' => 'National',
        'excerpt' => '500 jeunes paralegals formés et 20 centres d\'aide juridique gratuite dans les zones reculées pour garantir l\'accès à la justice.',
        'en_excerpt' => '500 trained youth paralegals and 20 free legal aid centres in remote areas to improve access to justice.',
        'en_geography' => 'National',
        'launched_at' => '2026-03-01',
        'duration_months' => 24,
        'progress_percent' => 5,
    ],
    [
        'slug' => 'sante-maternelle',
        'file' => 'sante-maternelle.html',
        'sectors_upper' => ['HEALTH', 'TERRITORIES'],
        'status' => 'actif',
        'volunteers' => 112,
        'budget_display' => '780 M Ar',
        'geography' => 'ATSIMO ANDREFANA',
        'excerpt' => 'Renforcer 100 cliniques rurales pour réduire la mortalité maternelle de 40 % en 3 ans — équipements, formations, protocoles.',
        'en_excerpt' => 'Strengthen 100 rural clinics to reduce maternal mortality by 40% in three years — equipment, training, protocols.',
        'en_geography' => 'ATSIMO ANDREFANA',
        'launched_at' => '2026-04-01',
        'duration_months' => 36,
        'progress_percent' => 18,
    ],
];

function extractProjectMain(string $html): ?string
{
    if (preg_match('#<div class="project-main">(.+?)</div>\s*<!--\s*/project-main\s*-->#s', $html, $m)) {
        return trim($m[1]);
    }

    return null;
}

function metaDescription(string $html): string
{
    if (preg_match('#<meta\s+name="description"\s+content="([^"]*)"#i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return '';
}

function h1Title(string $html): string
{
    if (preg_match('#<h1>([^<]+)</h1>#', $html, $m)) {
        return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    return '';
}

/**
 * @param callable(string): string $sqlEscape
 * @param array<string, mixed>      $p
 */
function append_project_seed_insert(array &$lines, callable $sqlEscape, string $now, array $p): void
{
    $bodyHtml   = (string) ($p['body'] ?? '');
    $blocksJson = ProjectBodyHtmlToBlocks::toJson($bodyHtml);
    $blocks     = json_decode($blocksJson, true);
    $useBlocks  = is_array($blocks) && $blocks !== [];

    $lines[] = 'INSERT INTO project_projects ('
        . 'slug, locale, translation_group, title, excerpt, body, body_content_mode, body_blocks, '
        . 'project_status, publication_state, sectors_csv, volunteers_count, budget_display, geography, '
        . 'launched_at, duration_months, progress_percent, meta_title, meta_description, published_at, '
        . 'created_at, updated_at, deleted_at'
        . ') VALUES (';
    $lines[] = "  '" . $sqlEscape((string) $p['slug']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['locale']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['translation_group']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['title']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['excerpt']) . "',";
    $lines[] = $useBlocks ? '  NULL,' : "  '" . $sqlEscape($bodyHtml) . "',";
    $lines[] = $useBlocks ? "  'blocks'," : "  'html',";
    $lines[] = $useBlocks ? "  '" . $sqlEscape($blocksJson) . "'," : '  NULL,';
    $lines[] = "  '" . $sqlEscape((string) $p['project_status']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['publication_state']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['sectors_csv']) . "',";
    $lines[] = '  ' . (int) $p['volunteers_count'] . ',';
    $lines[] = "  '" . $sqlEscape((string) $p['budget_display']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['geography']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['launched_at']) . "',";
    $lines[] = '  ' . (int) $p['duration_months'] . ',';
    $lines[] = '  ' . (int) $p['progress_percent'] . ',';
    $lines[] = "  '" . $sqlEscape((string) $p['meta_title']) . "',";
    $lines[] = "  '" . $sqlEscape((string) $p['meta_description']) . "',";
    $lines[] = (string) $p['published_at_sql'] . ',';
    $lines[] = "  '" . $sqlEscape($now) . "',";
    $lines[] = "  '" . $sqlEscape($now) . "',";
    $lines[] = '  NULL';
    $lines[] = ');';
    $lines[] = '';
}

$sqlEscape = static function (string $s): string {
    return str_replace(["\\", "'", "\0"], ["\\\\", "''", "\\0"], $s);
};

$lines = [];
$lines[] = '-- Seed project_projects depuis site_govgenz/projects-govgenz (FR) + database/project_seed_en (EN).';
$lines[] = '-- Même translation_group (slug FR) pour lier les paires FR/EN.';
$lines[] = '-- Prérequis : migrations appliquées (table project_projects + table sectors avec codes en minuscules).';
$lines[] = '-- Charset : utf8mb4.';
$lines[] = '';
$lines[] = 'SET NAMES utf8mb4;';
$lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$lines[] = '';
$lines[] = 'DELETE FROM project_projects WHERE locale IN (\'fr\', \'en\') AND slug IN ('
    . implode(', ', array_map(static fn (array $c): string => "'" . $sqlEscape($c['slug']) . "'", $cards))
    . ');';
$lines[] = '';

$now = date('Y-m-d H:i:s');

foreach ($cards as $c) {
    $path = $staticDir . '/' . $c['file'];
    if (! is_readable($path)) {
        fwrite(STDERR, "Fichier manquant : {$path}\n");
        exit(1);
    }
    $html = (string) file_get_contents($path);
    $main = extractProjectMain($html);
    if ($main === null || $main === '') {
        fwrite(STDERR, "project-main introuvable : {$c['file']}\n");
        exit(1);
    }
    $body = '<div class="project-main">' . $main . '</div>';
    $metaDesc = metaDescription($html);
    $title = h1Title($html);
    if ($title === '') {
        $title = $c['slug'];
    }

    $sectorsLower = array_map(static function (string $u) use ($sectorMap): string {
        return $sectorMap[$u] ?? strtolower($u);
    }, $c['sectors_upper']);
    $sectorsCsv = implode(',', $sectorsLower);

    $pub = in_array($c['status'], ['actif', 'complete'], true) ? 'published' : 'draft';
    $publishedAtSql = $pub === 'published' ? "'" . $sqlEscape($now) . "'" : 'NULL';

    $metaTitle = mb_strlen($title) <= 255 ? $title : mb_substr($title, 0, 252) . '…';

    $tg = (string) $c['slug'];

    append_project_seed_insert($lines, $sqlEscape, $now, [
        'slug'               => $tg,
        'locale'             => 'fr',
        'translation_group'  => $tg,
        'title'              => $title,
        'excerpt'            => (string) $c['excerpt'],
        'body'               => $body,
        'project_status'     => (string) $c['status'],
        'publication_state'  => $pub,
        'sectors_csv'        => $sectorsCsv,
        'volunteers_count'   => (int) $c['volunteers'],
        'budget_display'     => (string) $c['budget_display'],
        'geography'          => (string) $c['geography'],
        'launched_at'        => (string) $c['launched_at'],
        'duration_months'    => (int) $c['duration_months'],
        'progress_percent'   => (int) $c['progress_percent'],
        'meta_title'         => $metaTitle,
        'meta_description'   => mb_substr($metaDesc, 0, 512),
        'published_at_sql'   => $publishedAtSql,
    ]);

    $enPath = $enHtmlDir . '/' . $c['file'];
    if (! is_readable($enPath)) {
        fwrite(STDERR, "Fichier EN manquant : {$enPath}\n");
        exit(1);
    }
    $enHtml = (string) file_get_contents($enPath);
    $enMain  = extractProjectMain($enHtml);
    if ($enMain === null || $enMain === '') {
        fwrite(STDERR, "project-main introuvable (EN) : {$c['file']}\n");
        exit(1);
    }
    $enBody     = '<div class="project-main">' . $enMain . '</div>';
    $enMetaDesc = metaDescription($enHtml);
    $enTitle    = h1Title($enHtml);
    if ($enTitle === '') {
        $enTitle = $tg . ' (EN)';
    }
    $enMetaTitle = mb_strlen($enTitle) <= 255 ? $enTitle : mb_substr($enTitle, 0, 252) . '…';

    append_project_seed_insert($lines, $sqlEscape, $now, [
        'slug'               => $tg,
        'locale'             => 'en',
        'translation_group'  => $tg,
        'title'              => $enTitle,
        'excerpt'            => (string) $c['en_excerpt'],
        'body'               => $enBody,
        'project_status'     => (string) $c['status'],
        'publication_state'  => $pub,
        'sectors_csv'        => $sectorsCsv,
        'volunteers_count'   => (int) $c['volunteers'],
        'budget_display'     => (string) $c['budget_display'],
        'geography'          => (string) $c['en_geography'],
        'launched_at'        => (string) $c['launched_at'],
        'duration_months'    => (int) $c['duration_months'],
        'progress_percent'   => (int) $c['progress_percent'],
        'meta_title'         => $enMetaTitle,
        'meta_description'   => mb_substr($enMetaDesc, 0, 512),
        'published_at_sql'   => $publishedAtSql,
    ]);
}

$lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$lines[] = '';

file_put_contents($outFile, implode("\n", $lines));
echo "Écrit : {$outFile}\n";
