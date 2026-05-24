<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectGeographyData extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('project_projects', [
            'geography_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('project_projects', 'geography_data');
    }
}
