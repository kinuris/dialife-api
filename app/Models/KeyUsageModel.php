<?php

namespace App\Models;

use CodeIgniter\Model;

class KeyUsageModel extends Model
{
    protected $table            = 'tbl_key_usage';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $allowedFields    = [
        'key_usage_id',
        'fk_registration_key_id',
        'fk_doctor_id',
        'created_at'
    ];
}
