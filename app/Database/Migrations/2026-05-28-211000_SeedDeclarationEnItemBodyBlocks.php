<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fiches déclaration (EN) : corps détaillé en blocs pour les 3 déclarations + 3 partenariats.
 */
class SeedDeclarationEnItemBodyBlocks extends Migration
{
    /** @var array<string, list<array<string, mixed>>> */
    private const BLOCKS_BY_SLUG = [
        'youth-public-decisions-advocacy' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Why youth inclusion matters',
                'heading_style'    => 'warm',
                'intro'            => 'More than half of Madagascar\'s population is under 25. Yet youth remain under-represented in budget debates, legislative commissions and local planning bodies that shape their future.',
                'bullets'          => [
                    '55% of Malagasy are young — INSTAT demographic projections',
                    'Few participatory budget processes include under-30 delegates',
                    'Advisory councils rarely publish minutes or citizen feedback',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'What we ask public institutions',
                'heading_style'    => 'teal',
                'intro'            => 'GoV Gen Z Madagascar calls for concrete, measurable inclusion mechanisms — not symbolic youth forums.',
                'bullets'          => [
                    'Reserve seats for youth delegates on legislative committees handling education, employment and digital policy',
                    'Launch participatory budget pilots in at least 5 regions with published results',
                    'Publish advisory council agendas, minutes and follow-up within 30 days',
                    'Track youth inclusion indicators in annual governance reports',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Join the advocacy',
                'heading_style' => 'teal',
                'message'       => 'Institutions, media and citizens: contact us to co-sign this advocacy or propose a concrete inclusion mechanism in your sector.',
                'submessage'    => 'contact@govgenz.org · Subject: Youth inclusion advocacy',
            ],
            [
                'type'          => 'sources',
                'section_title' => 'Sources & references',
                'lines'         => [
                    'INSTAT — Madagascar demographic outlook (2024–2026 projections)',
                    'UNDP — Youth, governance and civic participation in Africa (2023)',
                    'GoV Gen Z Madagascar — internal sector mapping, governance programme (2026)',
                ],
            ],
        ],
        'principles-declaration-2026' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Our public commitments',
                'heading_style'    => 'teal',
                'intro'            => 'This principles declaration sets the ethical baseline for every position, project and partnership announced by GoV Gen Z Madagascar.',
                'bullets'          => [
                    'Document every public decision with verifiable sources before advocacy',
                    'Reject opaque deals that bypass community consultation',
                    'Prioritise Malagasy sovereignty in digital, natural resources and education policy',
                    'Publish partnership terms once agreements are signed',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'What this means in practice',
                'heading_style'    => 'warm',
                'intro'            => '',
                'bullets'          => [],
                'extra_paragraphs' => [
                    'Every declaration card on this page links to a documented detail page — summary in the hero, full context in blocks below.',
                    'Positions and projects follow the same standard: no anonymous claims, no unverifiable statistics.',
                    'When we lack data, we say so publicly and invite experts to contribute.',
                ],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Living document',
                'heading_style' => 'warm',
                'message'       => 'These principles are reviewed by the movement\'s governance bodies and updated when our field work reveals new ethical requirements.',
                'submessage'    => 'Last reviewed: June 2026',
            ],
        ],
        'alert-opaque-mining' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Background',
                'heading_style'    => 'warm',
                'intro'            => 'Mining contracts signed without adequate public consultation undermine community trust and fiscal transparency. Local populations often learn about concessions only after operations begin.',
                'bullets'          => [],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'Our concerns',
                'heading_style'    => 'warm',
                'intro'            => '',
                'bullets'          => [
                    'Contract terms not published in full before parliamentary ratification',
                    'Insufficient disclosure on revenue sharing with affected communities',
                    'Environmental impact assessments not accessible in local languages',
                    'No independent audit trail on royalty payments',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'What we demand',
                'heading_style'    => 'teal',
                'intro'            => 'GoV Gen Z Madagascar calls for immediate transparency measures before any new mining concession is ratified.',
                'bullets'          => [
                    'Publish full contract texts and annexes online',
                    'Hold public hearings in affected regions with documented minutes',
                    'Establish an independent community benefit monitoring mechanism',
                    'Align disclosure standards with EITI principles',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'sources',
                'section_title' => 'Sources',
                'lines'         => [
                    'EITI — Standard for transparency in extractive industries',
                    'World Bank — Governance of natural resources (Madagascar country brief)',
                    'GoV Gen Z Madagascar — Natural resources sector team field notes (2026)',
                ],
            ],
        ],
        'partnership-donors' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Partnership scope',
                'heading_style'    => 'teal',
                'intro'            => 'We seek technical and financial partners aligned with our six priority projects: governance, education, digital sovereignty, health, environment and youth employment.',
                'bullets'          => [
                    'Multi-year funding with public reporting requirements',
                    'Co-design with Malagasy youth-led teams — not top-down templates',
                    'Transparency on overhead and local subcontracting',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Propose a partnership',
                'heading_style' => 'teal',
                'message'       => 'International organizations, development agencies and bilateral institutions: reach out with a brief concept note. Signed partnerships will be announced on this page.',
                'submessage'    => 'partnerships@govgenz.org',
            ],
        ],
        'citizen-alliance-call' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Who we invite',
                'heading_style'    => 'warm',
                'intro'            => 'CSOs, youth associations, citizen collectives and local media that share our values of ethical governance and documented advocacy.',
                'bullets'          => [
                    'Joint field missions and source verification',
                    'Shared training on public policy analysis',
                    'Coalition statements with named signatories',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Join the coalition',
                'heading_style' => 'warm',
                'message'       => 'Tell us about your organization, geographic focus and one concrete collaboration idea. We respond within 10 business days.',
                'submessage'    => 'partnerships@govgenz.org · Subject: Citizen alliance',
            ],
        ],
        'institutional-partnership' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Institutional collaboration',
                'heading_style'    => 'teal',
                'intro'            => 'Universities, research centres and diplomatic missions can help ground our positions in rigorous analysis and international best practice.',
                'bullets'          => [
                    'Expert review of sector positions before publication',
                    'Student research partnerships with credited outputs',
                    'Public lectures and policy briefings in Antananarivo and regions',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Contact',
                'heading_style' => 'teal',
                'message'       => 'Embassies, faculties and research institutes: introduce your institution and proposed collaboration format.',
                'submessage'    => 'partnerships@govgenz.org · Subject: Institutional partnership',
            ],
        ],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }
        if (! $this->db->fieldExists('body_blocks', 'declaration_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (self::BLOCKS_BY_SLUG as $slug => $blocks) {
            $row = $this->db->table('declaration_items')
                ->where('slug', $slug)
                ->where('locale', 'en')
                ->get()
                ->getRowArray();

            if ($row === null) {
                continue;
            }

            $existing = trim((string) ($row['body_blocks'] ?? ''));
            if ($existing !== '' && $existing !== '[]') {
                continue;
            }

            $this->db->table('declaration_items')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'body'              => null,
                    'body_content_mode' => 'blocks',
                    'body_blocks'       => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                    'updated_at'        => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (array_keys(self::BLOCKS_BY_SLUG) as $slug) {
            $this->db->table('declaration_items')
                ->where('slug', $slug)
                ->where('locale', 'en')
                ->update([
                    'body'              => null,
                    'body_content_mode' => 'blocks',
                    'body_blocks'       => null,
                    'updated_at'        => $now,
                ]);
        }
    }
}
