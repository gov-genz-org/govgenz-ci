<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCmsHomeBlocksTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'section' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'eyebrow' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'subtitle' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'body_html' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'link_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'link_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'payload_json' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('section');
        $this->forge->addKey(['section', 'sort_order']);
        $this->forge->createTable('cms_home_blocks');

        $this->seedDefaults();
    }

    public function down(): void
    {
        $this->forge->dropTable('cms_home_blocks', true);
    }

    private function seedDefaults(): void
    {
        $now = date('Y-m-d H:i:s');

        $heroPayload = [
            'trust' => [
                'Noyau exécutif & coordination',
                '14 équipes sectorielles',
                'Impacts mesurables',
            ],
            'actions' => [
                ['label' => 'Découvrir le mouvement', 'url' => '#qui-sommes-nous', 'variant' => 'primary'],
                ['label' => 'Nous écrire', 'url' => 'contact', 'variant' => 'secondary'],
                ['label' => 'Rejoindre', 'url' => 'join', 'variant' => 'secondary'],
            ],
        ];

        $rows = [
            [
                'section' => 'hero',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => 'Programme Paikady Taninjanaka',
                'title' => 'GoV Gen Z Madagascar',
                'subtitle' => 'Mouvement structuré pour bâtir un avenir digne, serein et durable — dignité et sérénité pour le peuple, un avenir meilleur pour la jeunesse et les générations futures.',
                'body_html' => null,
                'meta_title' => 'GoV Gen Z Madagascar',
                'meta_description' => 'Programme Paikady Taninjanaka — mouvement citoyen pour la jeunesse et l’avenir de Madagascar.',
                'payload_json' => json_encode($heroPayload, JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'editorial',
                'sort_order' => 0,
                'is_active' => 0,
                'eyebrow' => null,
                'title' => null,
                'subtitle' => null,
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'qui_intro',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => 'Qui sommes-nous',
                'title' => 'Pour eux, avec vous',
                'subtitle' => 'Nous nous battons pour cinq cercles : ceux qui construisent aujourd’hui, ceux qui hériteront demain, et celles et ceux qui nous soutiennent partout dans le monde.',
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'qui_footer',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => null,
                'title' => null,
                'subtitle' => null,
                'body_html' => '<p>Source indicative · étude démographique et jeunesse (réf. publique govgenz.org).</p>',
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'adn_intro',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => 'L’ADN de GoV Gen Z Madagascar',
                'title' => 'Ce qui nous porte',
                'subtitle' => 'Quatre piliers qui définissent qui nous sommes, ce que nous voulons, et comment nous y allons.',
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'structure_intro',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => 'Notre structure',
                'title' => 'Une organisation lisible',
                'subtitle' => 'Un noyau exécutif central, des fonctions transversales, et des équipes sectorielles joignables pour avancer concrètement.',
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'secteurs_intro',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => '14 équipes sectorielles',
                'title' => 'Bâtir secteur par secteur',
                'subtitle' => 'Quatorze domaines d’action — contactez l’équipe concernée pour contribuer ou proposer un projet.',
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'etude_intro',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => 'Étude jeunesse',
                'title' => 'Les chiffres qui nous portent',
                'subtitle' => 'Une base chiffrée pour comprendre le poids démographique de la jeunesse et les leviers à activer.',
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => json_encode([
                    'actions' => [
                        ['label' => 'Demander l’étude complète', 'url' => 'contact', 'variant' => 'secondary'],
                        ['label' => 'Voir les communiqués', 'url' => 'press', 'variant' => 'primary'],
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'section' => 'rejoindre',
                'sort_order' => 0,
                'is_active' => 1,
                'eyebrow' => 'Rejoindre le mouvement',
                'title' => 'Notre avenir se défend maintenant',
                'subtitle' => 'Jeunesse, diaspora, sympathisants, experts, journalistes ou partenaires : passez par les portails qui correspondent à votre engagement.',
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => json_encode([
                    'tiles' => [
                        ['title' => 'Contact général', 'href' => 'mailto:contact@govgenz.org', 'hint' => 'Premier contact, questions générales.'],
                        ['title' => 'Rejoindre', 'href' => 'join', 'hint' => 'Membre actif, secteur ou région.'],
                        ['title' => 'Partenariat', 'href' => 'mailto:partnerships@govgenz.org', 'hint' => 'Organisations, alliances stratégiques.'],
                        ['title' => 'Presse', 'href' => 'mailto:communication@govgenz.org', 'hint' => 'Médias nationaux et internationaux.'],
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $stats = [
            ['title' => 'Enfants', 'subtitle' => '0–17 ans · 48,5 %', 'body_html' => '<p>L’avenir du pays se joue dès aujourd’hui.</p>', 'payload' => ['value' => '12,44', 'unit' => 'M']],
            ['title' => 'Jeunesse', 'subtitle' => '14–30 ans · 33,8 %', 'body_html' => '<p>Cœur de la mobilisation et de la construction.</p>', 'payload' => ['value' => '8,68', 'unit' => 'M']],
            ['title' => 'Relève', 'subtitle' => 'Générations futures', 'body_html' => '<p>Bâtir un héritage qui les protège.</p>', 'payload' => ['value' => '∞', 'unit' => '']],
            ['title' => 'Diaspora', 'subtitle' => 'Malgaches du monde', 'body_html' => '<p>Compétences, mentorat, plaidoyer international.</p>', 'payload' => ['value' => '∞', 'unit' => '']],
            ['title' => 'Sympathisants', 'subtitle' => 'Celles et ceux qui soutiennent', 'body_html' => '<p>Toutes les énergies bienveillantes.</p>', 'payload' => ['value' => '∞', 'unit' => '']],
        ];
        foreach ($stats as $i => $st) {
            $rows[] = [
                'section' => 'stats',
                'sort_order' => $i,
                'is_active' => 1,
                'eyebrow' => null,
                'title' => $st['title'],
                'subtitle' => $st['subtitle'],
                'body_html' => $st['body_html'],
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => json_encode($st['payload'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $pillars = [
            [
                'title' => 'Notre raison d’être',
                'payload' => [
                    'step' => '01 · Pour qui',
                    'items' => [
                        '8,68 M de jeunes (14–30 ans)',
                        '12,44 M d’enfants (0–17 ans)',
                        'La diaspora malgache mondiale',
                        'Les générations futures',
                    ],
                ],
            ],
            [
                'title' => 'Nos valeurs',
                'payload' => [
                    'step' => '02 · Ce qui nous guide',
                    'items' => ['Intégrité · Éthique', 'Entraide · Harmonie', 'Vitesse · Efficacité', 'Servir la cause'],
                ],
            ],
            [
                'title' => 'Notre méthode',
                'payload' => [
                    'step' => '03 · Comment',
                    'items' => ['Intelligence collective', 'Co-construction citoyenne', '15 % réfléchir · 85 % agir', 'Impacts mesurables et utiles'],
                ],
            ],
            [
                'title' => 'Notre but',
                'payload' => [
                    'step' => '04 · Pour quoi',
                    'items' => ['Dignité et sérénité', 'Souveraineté nationale', 'Système au service du peuple', 'Avenir meilleur — durable'],
                ],
            ],
        ];
        foreach ($pillars as $i => $p) {
            $rows[] = [
                'section' => 'pillars',
                'sort_order' => $i,
                'is_active' => 1,
                'eyebrow' => null,
                'title' => $p['title'],
                'subtitle' => null,
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => json_encode($p['payload'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $structures = [
            ['title' => 'Noyau exécutif', 'subtitle' => 'Coordination · Vision · Décision · sécurité du cadre.', 'payload' => ['email' => 'contact@govgenz.org']],
            ['title' => 'Coordination', 'subtitle' => 'Exécutifs · Sectorielle · Régions · Diaspora.', 'payload' => ['email' => 'coordination@govgenz.org']],
            ['title' => 'Communication', 'subtitle' => 'Stratégie · Contenus · Réseaux · Vulgarisation.', 'payload' => ['email' => 'communication@govgenz.org']],
            ['title' => 'Partenariats', 'subtitle' => 'National et international.', 'payload' => ['email' => 'partnerships@govgenz.org']],
            ['title' => 'Ressources humaines', 'subtitle' => 'Recrutement · Onboarding · Formation.', 'payload' => ['email' => 'recruitment@govgenz.org']],
            ['title' => 'Projets & finances', 'subtitle' => 'PMO · Impact · KPI · Trésorerie.', 'payload' => ['emails' => ['projects@govgenz.org', 'finance@govgenz.org']]],
        ];
        foreach ($structures as $i => $s) {
            $rows[] = [
                'section' => 'structure_cards',
                'sort_order' => $i,
                'is_active' => 1,
                'eyebrow' => null,
                'title' => $s['title'],
                'subtitle' => $s['subtitle'],
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => json_encode($s['payload'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $sectorRows = [
            ['LEGAL', 'Justice · Gouvernance · Anti-corruption', 'legal@govgenz.org'],
            ['ECONOMY', 'Finances publiques · Commerce · Emploi', 'economy@govgenz.org'],
            ['FOOD', 'Agriculture · Pêche · Souveraineté alimentaire', 'food@govgenz.org'],
            ['ENERGY', 'Énergies renouvelables · Solaire · Éolien', 'energy@govgenz.org'],
            ['WATER', 'Eau et assainissement · Accès · Qualité', 'water@govgenz.org'],
            ['EDUCATION', 'Formation · Recherche · Innovation', 'education@govgenz.org'],
            ['HEALTH', 'Santé · Nutrition · Protection sociale', 'health@govgenz.org'],
            ['INFRASTRUCTURE', 'Transport · Désenclavement', 'infrastructure@govgenz.org'],
            ['DIGITAL', 'Numérique · Données · IA', 'digital@govgenz.org'],
            ['TERRITORIES', 'Décentralisation · Foncier · Logement', 'territories@govgenz.org'],
            ['ENVIRONMENT', 'Climat · Ressources naturelles', 'environment@govgenz.org'],
            ['MINES', 'Ressources minières · Traçabilité', 'mines@govgenz.org'],
            ['SECURITY', 'Sécurité civile · Gestion de crise', 'security@govgenz.org'],
            ['CITIZEN', 'Jeunesse · Culture · Diaspora', 'citizen@govgenz.org'],
        ];
        foreach ($sectorRows as $i => $sr) {
            $rows[] = [
                'section' => 'sectors',
                'sort_order' => $i,
                'is_active' => 1,
                'eyebrow' => null,
                'title' => $sr[1],
                'subtitle' => null,
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => json_encode(['code' => $sr[0], 'email' => $sr[2]], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $metrics = [
            ['value' => '72,6 %', 'label' => 'de la population a 0–30 ans'],
            ['value' => '75,2 %', 'label' => 'de pauvreté nationale en 2022'],
            ['value' => '47 %', 'label' => 'des 5–17 ans concernés par le travail des enfants'],
            ['value' => '13 %', 'label' => 'de fréquentation au secondaire second cycle'],
        ];
        foreach ($metrics as $i => $m) {
            $rows[] = [
                'section' => 'metrics',
                'sort_order' => $i,
                'is_active' => 1,
                'eyebrow' => null,
                'title' => null,
                'subtitle' => null,
                'body_html' => null,
                'meta_title' => null,
                'meta_description' => null,
                'payload_json' => json_encode($m, JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->db->table('cms_home_blocks')->insertBatch($rows);
    }
}
