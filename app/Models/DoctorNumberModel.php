<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorNumberModel extends Model
{
    protected $table            = 'tbl_doctor_number';
    protected $primaryKey       = 'doctor_number_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'doctor_number_id', 
        'fk_doctor_id', 
        'number',
    ];
}
