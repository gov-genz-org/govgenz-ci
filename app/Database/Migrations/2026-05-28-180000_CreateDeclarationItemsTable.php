<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeclarationItemsTable extends Migration
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
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'default'    => 'fr',
            ],
            'translation_group' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kind' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'official',
            ],
            'list_section' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'declarations',
            ],
            'meta_line' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'default'    => '',
            ],
            'band_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'default'    => '',
            ],
            'badge_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'default'    => '',
            ],
            'cta_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'default'    => '',
            ],
            'cta_href' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'publication_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'draft',
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['slug', 'locale']);
        $this->forge->addKey(['publication_state', 'locale', 'list_section', 'sort_order']);
        $this->forge->createTable('declaration_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('declaration_items', true);
    }
}
