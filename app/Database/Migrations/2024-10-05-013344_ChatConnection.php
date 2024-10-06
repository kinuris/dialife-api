<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChatConnection extends Migration
{
    public function up()
    {
        $this->forge->addField('chat_connection_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');
        $this->forge->addField('fk_doctor_id INT NOT NULL');
        $this->forge->addField('fk_patient_id INT NOT NULL');

        $this->forge->addForeignKey('fk_doctor_id', 'tbl_doctors', 'doctor_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('fk_patient_id', 'tbl_patient', 'patient_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('tbl_chat_connection');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_chat_connection');
    }
}
