<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedPositionItemEnDemo extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('position_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $blocksEn = json_encode([
            [
                'type'          => 'section_rich',
                'heading'       => '📰 Official context',
                'heading_style' => 'warm',
                'intro'         => 'The government announced a five-year education plan funded at 500 billion Ariary, promising 2,000 new classrooms, tablets for 500 schools, and higher teacher pay.',
                'bullets'       => [],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'section_rich',
                'heading'       => '🔍 What the announcement omits',
                'heading_style' => 'warm',
                'intro'         => '',
                'bullets'       => [
                    'What we see: a 500 bn Ar budget and construction figures',
                    'What we do not see: 67% of rural schools without electricity',
                    'Critical gap: no pedagogical training for teachers',
                    'Financial risk: no independent budget tracking or audit mechanism',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'section_rich',
                'heading'       => '✅ GoV Gen Z Madagascar’s position',
                'heading_style' => 'teal',
                'intro'         => 'Tablets in schools without power are not an education plan. We call for a revised plan with three measurable pillars: solar electrification, teacher training, and public accountability.',
                'bullets'       => [
                    'Pillar 1 — Priority school electrification: 180 bn Ar · 18 months',
                    'Pillar 2 — Pedagogical training: 120 bn Ar · 24 months',
                    'Pillar 3 — Accountability mechanism: 30 bn Ar · ongoing',
                ],
                'extra_paragraphs' => [],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $exists = $this->db->table('position_items')
            ->where('slug', 'national-education-plan-2026-2030')
            ->where('locale', 'en')
            ->get()
            ->getFirstRow() !== null;

        if ($exists) {
            return;
        }

        $this->db->table('position_items')->insert([
            'slug'                => 'national-education-plan-2026-2030',
            'locale'              => 'en',
            'translation_group'   => 'plan-education-2026',
            'title'               => 'National education plan 2026–2030: what the announcement omits',
            'excerpt'             => 'Alert on the 2026–2030 Education plan and a proposal of three costed pillars.',
            'summary'             => 'Tablets in schools without electricity are not an education plan. GoV Gen Z proposes a sequenced plan in three costed pillars: solar electrification first, mandatory pedagogical training for 15,000 teachers, and a public accountability mechanism.',
            'body'                => null,
            'body_content_mode'   => 'blocks',
            'body_blocks'         => $blocksEn,
            'types_csv'           => 'denial,solution',
            'sectors_csv'         => 'education,digital',
            'reading_minutes'     => 6,
            'publication_state'   => 'published',
            'meta_title'          => 'Education plan 2026–2030 — GoV Gen Z',
            'meta_description'    => 'Analysis and counter-proposal on the national education plan 2026–2030.',
            'published_at'        => '2026-05-12 10:00:00',
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('position_items')) {
            return;
        }

        $this->db->table('position_items')
            ->where('slug', 'national-education-plan-2026-2030')
            ->where('locale', 'en')
            ->delete();
    }
}
