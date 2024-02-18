<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientRecordModel extends Model
{
    protected $table            = 'tbl_patient_record';
    protected $primaryKey       = 'patient_record_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'patient_record_id', 
        'blood_glucose_level', 
        'bmi_level',
        'activity_type',
        'activity_duration',
        'activity_frequency',
        'nutrition_protein',
        'nutrition_fat',
        'nutrition_carbohydrates',
        'nutrition_water',
        'fk_patient_id',
        'created_at'
    ];

}
