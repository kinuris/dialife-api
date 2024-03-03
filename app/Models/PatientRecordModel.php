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
        // TODO: Add corresponding timestamps to record data
        'patient_record_id', 
        'glucose_created_at',
        'blood_glucose_level', 
        'bmi_created_at',
        'bmi_level',
        'activity_created_at',
        'activity_type',
        'activity_duration',
        'activity_frequency',
        'nutrition_created_at',
        'nutrition_protein',
        'nutrition_fat',
        'nutrition_carbohydrates',
        'nutrition_water',
        'medicine_route',
        'medicine_name',
        'medicine_form',
        'medicine_dosage',
        'medicine_taken_at',
        'fk_patient_id',
        'created_at'
    ];

}
