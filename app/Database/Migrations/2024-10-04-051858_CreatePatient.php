<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePatient extends Migration
{
    public function up()
    {
        $this->forge->addField('patient_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');
        $this->forge->addField('name VARCHAR(255) NOT NULL');
        $this->forge->addField('birthdate DATETIME NOT NULL');
        $this->forge->addField('province VARCHAR(255) NOT NULL');
        $this->forge->addField('municipality VARCHAR(255) NOT NULL');
        $this->forge->addField('barangay VARCHAR(255) NOT NULL');
        $this->forge->addField('zip_code VARCHAR(4) NOT NULL');
        $this->forge->addField('sex ENUM("male", "female") NOT NULL');
        $this->forge->addField('address_description VARCHAR(255) NOT NULL');
        $this->forge->addField('contact_number VARCHAR(20) NOT NULL');

        $this->forge->createTable('tbl_patient');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_patient');
    }
}
