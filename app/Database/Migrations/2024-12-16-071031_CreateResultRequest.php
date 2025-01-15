<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResultRequest extends Migration
{
    public function up()
    {
        $this->forge->addField('result_request_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');

        $this->forge->addField('fk_doctor_id INT NOT NULL');
        $this->forge->addForeignKey('fk_doctor_id', 'tbl_doctors', 'doctor_id', 'CASCADE', 'CASCADE');

        $this->forge->addField('fk_patient_id INT NOT NULL');
        $this->forge->addForeignKey('fk_patient_id', 'tbl_patient', 'patient_id', 'CASCADE', 'CASCADE');

        $this->forge->addField('type VARCHAR(255) NOT NULL');

        $this->forge->addField('created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->forge->createTable('tbl_result_request');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_result_request');
    }
}
