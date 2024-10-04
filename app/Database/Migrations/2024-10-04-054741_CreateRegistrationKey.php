<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegistrationKey extends Migration
{
    public function up()
    {
        $this->forge->addField('registration_key_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');

        $this->forge->addField('key_string VARCHAR(34) NOT NULL');
        $this->forge->addField('used BOOL NOT NULL DEFAULT FALSE');

        $this->forge->createTable('tbl_registration_key');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_registration_key');
    }
}
