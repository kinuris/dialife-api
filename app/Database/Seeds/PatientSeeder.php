<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'John Doe',
                'birthdate' => '1985-07-15 00:00:00',
                'province' => 'Iloilo',
                'municipality' => 'Iloilo City',
                'barangay' => 'San Juan',
                'zip_code' => '5000',
                'sex' => 'male',
                'address_description' => '1234 Sample Street',
                'contact_number' => '09123456789',
            ],
            [
                'name' => 'Jane Smith',
                'birthdate' => '1990-02-20 00:00:00',
                'province' => 'Capiz',
                'municipality' => 'Roxas City',
                'barangay' => 'Baybay',
                'zip_code' => '5800',
                'sex' => 'female',
                'address_description' => '5678 Example Road',
                'contact_number' => '09234567890',
            ],
        ];

        $this->db->table('tbl_patient')->insertBatch($data);
    }
}
