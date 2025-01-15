<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('tbl_admin')->insert([
            'email' => 'admin@pulsepilot.com',
            'password' => password_hash('password', PASSWORD_ARGON2I),
        ]);
    }
}
