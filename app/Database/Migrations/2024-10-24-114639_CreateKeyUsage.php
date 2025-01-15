<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKeyUsage extends Migration
{
    public function up()
    {
        $this->forge->addField('key_usage_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');

        $this->forge->addField('fk_registration_key_id INT NOT NULL');
        $this->forge->addForeignKey('fk_registration_key_id', 'tbl_registration_key', 'registration_key_id', 'CASCADE', 'CASCADE');

        $this->forge->addField('fk_doctor_id INT NOT NULL');
        $this->forge->addForeignKey('fk_doctor_id', 'tbl_doctors', 'doctor_id', 'CASCADE', 'CASCADE');

        $this->forge->addField('created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->forge->createTable('tbl_key_usage');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_key_usage');
    }
}
