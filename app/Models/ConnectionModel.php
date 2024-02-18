<?php

namespace App\Models;

use CodeIgniter\Model;

class ConnectionModel extends Model
{
    protected $table            = 'tbl_connection';
    protected $primaryKey       = 'connection_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = ['connection_id', 'fk_doctor_id', 'fk_patient_id'];
}
