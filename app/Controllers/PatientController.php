<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConnectionModel;
use App\Models\PatientModel;
use App\Models\PatientRecordModel;
use App\Utils\Utils;
use CodeIgniter\HTTP\ResponseInterface;

class PatientController extends BaseController
{
    // NOTE: Gets a patient and returns data as JSON
    public function index($id)
    {
        $patientModel = new PatientModel();

        return $this->response
            ->setContentType("application/json")
            ->setJSON($patientModel->find($id));
    }

    public function sync_patient_state()
    {
        $post = $this->request->getJSON();

        if (
            !isset($post->first_name) ||
            !isset($post->middle_name) ||
            !isset($post->last_name) ||
            !isset($post->is_male) ||
            !isset($post->birth_date) ||
            !isset($post->province) ||
            !isset($post->municipality) ||
            !isset($post->barangay) ||
            !isset($post->address_description) ||
            !isset($post->zip_code) ||
            !isset($post->contact_number) ||
            !isset($post->web_id)
        ) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'invalid data shape'])
                ->setstatuscode(404);
        }

        // TODO: should send app secret key 

        $patientModel = new PatientModel();
        $patientModel->update($post->web_id, [
            'name' => $post->first_name . (empty($post->middle_name) ? "" : " " . $post->middle_name)  . " " . $post->last_name,
            'birthdate' => $post->birth_date,
            'province' => $post->province,
            'municipality' => $post->municipality,
            'barangay' => $post->barangay,
            'address_description' => $post->address_description,
            'zip_code' => $post->zip_code,
            'contact_number' => $post->contact_number,
            'sex' => $post->is_male ? 'male' : 'female',
        ]);

        return $this->response
            ->setcontenttype('application/json')
            ->setjson(['message' => 'Success'])
            ->setstatuscode(200);
    }

    public function upload_record()
    {
        $post = $this->request->getJSON();

        if (
            !isset($post->blood_glucose_level) ||
            !isset($post->bmi_level) ||
            !isset($post->activity_type) ||
            !isset($post->activity_duration) ||
            !isset($post->activity_frequency) ||
            !isset($post->nutrition_protein) ||
            !isset($post->nutrition_fat) ||
            !isset($post->nutrition_carbohydrates) ||
            !isset($post->nutrition_water) ||
            !isset($post->patient_id)
        ) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'invalid data shape'])
                ->setstatuscode(404);
        }

        // TODO: Implement security measures via public/private keys

        $recordModel = new PatientRecordModel();
        $recordModel->insert([
            'blood_glucose_level' => $post->blood_glucose_level,
            'bmi_level' => $post->bmi_level,
            'activity_type' => $post->activity_type,
            'activity_duration' => $post->activity_duration,
            'activity_frequency' => $post->activity_frequency,
            'nutrition_protein' => $post->nutrition_protein,
            'nutrition_fat' => $post->nutrition_fat,
            'nutrition_carbohydrates' => $post->nutrition_carbohydrates,
            'nutrition_water' => $post->nutrition_water,
            'fk_patient_id' => $post->patient_id,
        ]);

        return $this->response
            ->setcontenttype('application/json')
            ->setjson(['message' => 'Success'])
            ->setstatuscode(200);
    }

    public function get_recent_records($patientId, $recordCount)
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'Unauthorized'])
                ->setstatuscode(403);
        }

        $payload = Utils::parseJWT($jwt);
        if (!isset($payload)) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'Unauthorized'])
                ->setstatuscode(403);
        }

        $connectionModel = new ConnectionModel();
        $connections = $connectionModel
            ->where('fk_patient_id', $patientId)
            ->where('fk_doctor_id', $payload['id'])
            ->findAll();

        if (empty($connections)) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'Unauthorized'])
                ->setstatuscode(403);
        }

        $recordModel = new PatientRecordModel();
        $records = $recordModel
            ->orderBy('created_at')
            ->limit($recordCount)
            ->find();

        return $this->response
            ->setContentType('application/json')
            ->setJSON($records)
            ->setStatusCode(200);
    }

    public function revoke_doctor()
    {
        // TODO: Both patients and doctors can do this

        $post = $this->request->getJSON();

        if (!isset($post->connection_id)) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson([
                    'message' => 'invalid data shape',
                ])
                ->setstatuscode(404);
        }

        $connectionModel = new ConnectionModel();
        $connection = $connectionModel->find($post->connection_id);
        if (!isset($connection)) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'connection not found'])
                ->setstatuscode(404);
        }

        $connectionModel->delete($post->connection_id);

        return $this->response
            ->setcontenttype('application/json')
            ->setjson([
                'message' => 'Success',
                'doctor_id' => $connection['fk_doctor_id'],
                'patient_id' => $connection['fk_patient_id'],
            ])
            ->setstatuscode(200);
    }

    public function assign_doctor()
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'Unauthorized'])
                ->setstatuscode(403);
        }

        // TODO: Only a doctor can initiate this 
        // TODO: Patient may revoke

        $connectionModel = new ConnectionModel();
        $post = $this->request->getJSON();

        // NOTE: Check if connection already exists
        $connection = $connectionModel
            ->where('fk_doctor_id', $post->doctor_id)
            ->where('fk_patient_id', $post->patient_id)
            ->findAll();

        if (!empty($connection)) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'invalid data shape'])
                ->setstatuscode(404);
        }

        $connectionModel->insert(['fk_doctor_id' => $post->doctor_id, 'fk_patient_id' => $post->patient_id]);

        if (
            !isset($post->doctor_id) ||
            !isset($post->patient_id)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        return $this->response
            ->setcontenttype('application/json')
            ->setjson(['message' => 'Success'])
            ->setstatuscode(200);
    }

    public function create_patient()
    {
        $patientModel = new PatientModel();
        $post = $this->request->getJSON();

        if (
            !isset($post->name) ||
            !isset($post->birthdate) ||
            !isset($post->municipality) ||
            !isset($post->province) ||
            !isset($post->barangay) ||
            !isset($post->zip_code) ||
            !isset($post->sex) ||
            !isset($post->address_description) ||
            !isset($post->contact_number)
        ) {
            return $this->response
                ->setcontenttype('application/json')
                ->setjson(['message' => 'invalid data shape'])
                ->setstatuscode(404);
        }

        // TODO: Check key only found inside the dialife app

        $id = $patientModel->insert([
            'name' => $post->name,
            'birthdate' => $post->birthdate,
            'province' => $post->province,
            'municipality' => $post->municipality,
            'barangay' => $post->barangay,
            'zip_code' => $post->zip_code,
            'sex' => $post->sex,
            'address_description' => $post->address_description,
            'contact_number' => $post->contact_number,
        ]);

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'message' => 'Success',
                'web_id' => $id,
            ])
            ->setStatusCode(404);
    }
}
