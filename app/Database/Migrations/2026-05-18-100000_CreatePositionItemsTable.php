<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePositionItemsTable extends Migration
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
            'excerpt' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'body' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'body_content_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'default'    => 'blocks',
            ],
            'body_blocks' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'types_csv' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'default'    => '',
            ],
            'sectors_csv' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'reading_minutes' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'publication_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'draft',
            ],
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['slug', 'locale']);
        $this->forge->addKey(['publication_state', 'locale', 'published_at']);
        $this->forge->createTable('position_items', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('position_items', true);
    }
}
