<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table            = 'tbl_patient';
    protected $primaryKey       = 'patient_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'patient_id', 
        'name', 
        'birthdate', 
        'province', 
        'municipality', 
        'barangay', 
        'zip_code', 
        'sex', 
        'address_description', 
        'contact_number',
    ];
}
