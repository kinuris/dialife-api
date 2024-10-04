<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePatientRecord extends Migration
{
    public function up()
    {
        $this->forge->addField('patient_record_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT');

        $this->forge->addField('fk_patient_id INT NOT NULL');

        $this->forge->addField('blood_glucose_level DECIMAL(5,2)');
        $this->forge->addField('glucose_created_at DATETIME');

        $this->forge->addField('bmi_level DECIMAL(5,2)');
        $this->forge->addField('bmi_created_at DATETIME');

        $this->forge->addField('activity_type VARCHAR(15)');
        $this->forge->addField('activity_duration INT');
        $this->forge->addField('activity_frequency INT');
        $this->forge->addField('activity_created_at DATETIME');

        $this->forge->addField('medicine_taken_at DATETIME');
        $this->forge->addField('medicine_name VARCHAR(255)');
        $this->forge->addField('medicine_route VARCHAR(255)');
        $this->forge->addField('medicine_form VARCHAR(255)');
        $this->forge->addField('medicine_dosage DECIMAL(5,2)');
        $this->forge->addField('medicine_taken_time DATETIME');

        $this->forge->addField('nutrition_foods TEXT');
        $this->forge->addField('nutrition_meal_time VARCHAR(20)');
        $this->forge->addField('nutrition_created_at DATETIME');

        $this->forge->addField('water_glasses INT');
        $this->forge->addField('water_created_at DATETIME');

        $this->forge->addField('created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->forge->addForeignKey('fk_patient_id', 'tbl_patient', 'patient_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('tbl_patient_record');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_patient_record');
    }
}
