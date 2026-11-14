<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerAuthentication extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', [
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'phone',
            ],
        ]);

        $this->db->query('ALTER TABLE customers ADD UNIQUE KEY uq_customers_email (email)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE customers DROP INDEX uq_customers_email');
        $this->forge->dropColumn('customers', 'password');
    }
}
