<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdmin extends Migration
{
    public function up()
    {
        $this->forge->addField('admin_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');
        $this->forge->addField('email VARCHAR(255) UNIQUE NOT NULL');
        $this->forge->addField('password VARCHAR(255) NOT NULL');

        $this->forge->createTable('tbl_admin');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_admin');
    }
}
