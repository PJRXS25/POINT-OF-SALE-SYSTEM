<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthThrottlesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'attempt_key' => ['type' => 'CHAR', 'constraint' => 64],
            'attempts' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'window_started_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('attempt_key', true);
        $this->forge->addKey('updated_at');
        $this->forge->createTable('auth_throttles', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_throttles', true);
    }
}
