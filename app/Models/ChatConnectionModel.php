<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatConnectionModel extends Model
{
    protected $table            = 'tbl_chat_connection';
    protected $primaryKey       = 'chat_connection_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = ['chat_connection_id', 'fk_doctor_id', 'fk_patient_id'];
}
