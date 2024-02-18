<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorModel extends Model
{
    protected $table            = 'tbl_doctors';
    protected $primaryKey       = 'doctor_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'doctor_id', 
        'name', 
        'email', 
        'password', 
        'profile_picture_link',
    ];
}
