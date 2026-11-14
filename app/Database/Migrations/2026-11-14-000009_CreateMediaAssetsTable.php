<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMediaAssetsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'token' => ['type' => 'VARCHAR', 'constraint' => 64],
            'category' => ['type' => 'VARCHAR', 'constraint' => 30],
            'filename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'data' => ['type' => 'LONGBLOB'],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('token', true);
        $this->forge->addKey('category');
        $this->forge->createTable('media_assets', true);
    }

    public function down()
    {
        $this->forge->dropTable('media_assets', true);
    }
}
