<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDoctors extends Migration
{
    public function up()
    {
        $this->forge->addField('doctor_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');
        $this->forge->addField('name VARCHAR(255) NOT NULL');
        $this->forge->addField('email VARCHAR(255) UNIQUE NOT NULL');
        $this->forge->addField('password VARCHAR(255) NOT NULL');
        $this->forge->addField('profile_picture_link VARCHAR(255) NOT NULL');

        $this->forge->createTable('tbl_doctors');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_doctors');
    }
}
