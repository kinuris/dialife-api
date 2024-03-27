<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistrationKeyModel extends Model
{
    protected $table            = 'tbl_registration_key';
    protected $primaryKey       = 'registration_key_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'registration_key_id',
        'key_string',
        'used'
    ];
}
