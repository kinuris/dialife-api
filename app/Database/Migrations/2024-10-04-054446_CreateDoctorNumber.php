<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDoctorNumber extends Migration
{
    public function up()
    {
        $this->forge->addField('doctor_number_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');

        $this->forge->addField('fk_doctor_id INT NOT NULL');
        $this->forge->addField('number VARCHAR(50) NOT NULL');

        $this->forge->addForeignKey('fk_doctor_id', 'tbl_doctors', 'doctor_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('tbl_doctor_number');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_doctor_number');
    }
}
