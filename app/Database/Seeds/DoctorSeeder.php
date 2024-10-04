<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('tbl_doctors')->insert([
            'name' => 'CDPC',
            'email' => 'chanchrisdominic@gmail.com',
            'password' => password_hash('password', PASSWORD_ARGON2I),
            'profile_picture_link' => '',
        ]);
    }
}
