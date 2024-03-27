<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConnectionModel;
use App\Models\PatientModel;
use App\Models\PatientRecordModel;
use App\Utils\Utils;
use DateTime;
use stdClass;

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

    public function all()
    {
        $patientModel = new PatientModel();

        return $this->response
            ->setContentType("application/json")
            ->setJSON($patientModel->findAll());
    }


    public function sync_patient_state()
    {
        $post = $this->request->getJSON();

        if (
            !isset($post->first_name) ||
            !isset($post->middle_name) ||
            !isset($post->last_name) ||
            !isset($post->is_male) ||
            !isset($post->birthdate) ||
            !isset($post->province) ||
            !isset($post->municipality) ||
            !isset($post->barangay) ||
            !isset($post->address_description) ||
            !isset($post->zip_code) ||
            !isset($post->contact_number) ||
            !isset($post->web_id)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        // TODO: should send app secret key 
        $patientModel = new PatientModel();

        if (empty($patientModel->where("patient_id", $post->web_id)->find())) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'no such user'])
                ->setStatusCode(404);
        }

        $patientModel->update($post->web_id, [
            'name' => $post->first_name . (empty($post->middle_name) ? "" : " " . $post->middle_name[0] . ".")  . " " . $post->last_name,
            'birthdate' => $post->birthdate,
            'province' => $post->province,
            'municipality' => $post->municipality,
            'barangay' => $post->barangay,
            'address_description' => $post->address_description,
            'zip_code' => $post->zip_code,
            'contact_number' => $post->contact_number,
            'sex' => $post->is_male ? 'male' : 'female',
        ]);

        return $this->response
            ->setContentType('application/json')
            ->setJSON(['message' => 'Success'])
            ->setStatusCode(200);
    }

    public function get_latest_record()
    {
        $post = $this->request->getJSON();

        if (
            !isset($post->type) ||
            !isset($post->patient_id)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        switch ($post->type) {
            case "glucose":
                $createdAtString = "glucose_created_at";
                $relevantProperties = ['blood_glucose_level', 'glucose_created_at'];
                break;
            case "nutrition":
                $createdAtString = "nutrition_created_at";
                $relevantProperties = ['nutrition_created_at', 'nutrition_foods', 'nutrition_meal_time'];
                break;
            case "activity":
                $createdAtString = "activity_created_at";
                $relevantProperties = ['activity_type', 'activity_duration', 'activity_frequency', 'activity_created_at'];
                break;
            case "medication":
                $createdAtString = "medicine_taken_at";
                $relevantProperties = ['medicine_name', 'medicine_route', 'medicine_form', 'medicine_dosage', 'medicine_taken_at'];
                break;
            case "bmi":
                $createdAtString = "bmi_created_at";
                $relevantProperties = ['bmi_level', 'bmi_created_at'];
                break;
            default:
                return $this->response
                    ->setContentType('application/json')
                    ->setJSON(['message' => 'invalid type passed'])
                    ->setStatusCode(404);
        }

        $recordModel = new PatientRecordModel();
        $record = $recordModel->orderBy($createdAtString, 'DESC')
            ->where($createdAtString . " IS NOT NULL")
            ->where('fk_patient_id', $post->patient_id)
            ->limit(1)
            ->find();

        $stripped = new stdClass();

        if (count($record) < 1) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON($stripped);
        }

        $record = $record[0];

        if (isset($record[$createdAtString])) {
            foreach ($relevantProperties as $property) {
                $stripped->$property = $record[$property];
            }
        }

        return $this->response
            ->setContentType('application/json')
            ->setJSON($stripped);
    }

    public function get_records()
    {
        $post = $this->request->getJSON();

        if (
            !isset($post->type) ||
            !isset($post->days) ||
            !isset($post->patient_id)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        $daysBack = strtotime('-' . $post->days . ' days');

        switch ($post->type) {
            case "glucose":
                $createdAtString = "glucose_created_at";
                $relevantProperties = ['blood_glucose_level', 'glucose_created_at'];
                break;
            case "nutrition":
                $createdAtString = "nutrition_created_at";
                $relevantProperties = ['nutrition_created_at', 'nutrition_foods', 'nutrition_meal_time'];
                break;
            case "activity":
                $createdAtString = "activity_created_at";
                $relevantProperties = ['activity_type', 'activity_duration', 'activity_frequency', 'activity_created_at'];
                break;
            case "medication":
                $createdAtString = "medicine_taken_at";
                $relevantProperties = ['medicine_name', 'medicine_route', 'medicine_form', 'medicine_dosage', 'medicine_taken_at'];
                break;
            case "bmi":
                $createdAtString = "bmi_created_at";
                $relevantProperties = ['bmi_level', 'bmi_created_at'];
                break;
            default:
                return $this->response
                    ->setContentType('application/json')
                    ->setJSON(['message' => 'invalid type passed'])
                    ->setStatusCode(404);
        }

        $recordModel = new PatientRecordModel();
        $records = $recordModel->orderBy($createdAtString, 'DESC')
            ->where($createdAtString . ' > ' . "'" . date('Y/m/d H:i:s', $daysBack) . "'")
            ->where('fk_patient_id', $post->patient_id)
            ->findAll();

        $validRecords = array();
        foreach ($records as $record) {
            if (isset($record[$createdAtString])) {
                $stripped = new stdClass();
                foreach ($relevantProperties as $property) {
                    $stripped->$property = $record[$property];
                }

                array_push($validRecords, $stripped);
            }
        }

        $deduped = array();
        for ($i = 0; $i < count($validRecords); $i++) {
            if ($i === count($validRecords) - 1) {
                array_push($deduped, $validRecords[$i]);
                break;
            }

            foreach ($relevantProperties as $property) {
                $currentFields[$property] = $validRecords[$i]->$property;
                $nextFields[$property] = $validRecords[$i + 1]->$property;
            }

            if ($currentFields === $nextFields) {
                continue;
            }

            array_push($deduped, $validRecords[$i]);
        }

        return $this->response
            ->setContentType('application/json')
            ->setJSON($deduped);
    }

    public function upload_record()
    {
        $post = $this->request->getJSON();

        if (
            // !isset($post->glucose_created_at) ||
            // !isset($post->blood_glucose_level) ||
            // !isset($post->bmi_created_at) ||
            // !isset($post->bmi_level) ||
            // !isset($post->activity_created_at) ||
            // !isset($post->activity_type) ||
            // !isset($post->activity_duration) ||
            // !isset($post->activity_frequency) ||
            // !isset($post->nutrition_foods) ||
            // !isset($post->nutrition_created_at) ||
            // !isset($post->nutrition_meal_time) ||
            // !isset($post->medicine_name) ||
            // !isset($post->medicine_route) ||
            // !isset($post->medicine_form) ||
            // !isset($post->medicine_dosage) ||
            // !isset($post->medicine_taken_at) ||
            !isset($post->patient_id)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        // TODO: Implement security measures via public/private keys

        $recordModel = new PatientRecordModel();
        $latest = $recordModel
            ->where('fk_patient_id', $post->patient_id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (getenv("CI_ENVIRONMENT") === 'development') {
            date_default_timezone_set("Asia/Manila");
        }

        if (isset($latest)) {
            $latest = new DateTime($latest["created_at"]);
            $interval = $latest->diff(new DateTime());
            $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

            // if ($total_minutes < 10) {
            //     $remaining = 10 - $total_minutes;
            //     return $this->response
            //         ->setContentType('application/json')
            //         ->setJSON(["timeout_duration_minutes" => "$remaining"])
            //         ->setStatusCode(200);
            // }
        }

        $recordModel->insert([
            'blood_glucose_level' => $post->glucose_level,
            'bmi_level' => $post->bmi_level,
            'activity_type' => $post->activity_type,
            'activity_duration' => $post->activity_duration,
            'activity_frequency' => $post->activity_frequency,
            'nutrition_foods' => $post->foods,
            'nutrition_meal_time' => $post->meal_time,
            'fk_patient_id' => $post->patient_id,
            'glucose_created_at' => $post->glucose_created_at,
            'bmi_created_at' => $post->bmi_created_at,
            'nutrition_created_at' => $post->nutrition_created_at,
            'activity_created_at' => $post->activity_created_at,
            'medicine_name' => $post->medicine_name,
            'medicine_route' => $post->medicine_route,
            'medicine_form' => $post->medicine_form,
            'medicine_dosage' => $post->medicine_dosage,
            'medicine_taken_at' => $post->medicine_taken_at,
        ]);

        return $this->response
            ->setContentType('application/json')
            ->setJSON(['message' => 'Success'])
            ->setStatusCode(200);
    }

    public function get_recent_records($patientId, $recordCount)
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $payload = Utils::parseJWT($jwt);
        if (!isset($payload)) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $connectionModel = new ConnectionModel();
        $connections = $connectionModel
            ->where('fk_patient_id', $patientId)
            ->where('fk_doctor_id', $payload['id'])
            ->findAll();

        if (empty($connections)) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
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
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $post = $this->request->getJSON();

        if (!isset($post->doctor_id) || !isset($post->patient_id)) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON([
                    'message' => 'invalid data shape',
                ])
                ->setStatusCode(404);
        }

        $connectionModel = new ConnectionModel();
        // $connection = $connectionModel->find($post->connection_id);
        // if (!isset($connection)) {
        //     return $this->response
        //         ->setContentType('application/json')
        //         ->setJSON(['message' => 'connection not found'])
        //         ->setStatusCode(404);
        // }

        $connectionModel->where('fk_doctor_id', $post->doctor_id)
            ->where('fk_patient_id', $post->patient_id)
            ->delete();

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'message' => 'Success',
                'doctor_id' => $post->doctor_id,
                'patient_id' => $post->patient_id,
            ])
            ->setStatusCode(200);
    }

    public function assign_doctor()
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
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
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
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
            ->setContentType('application/json')
            ->setJSON(['message' => 'Success'])
            ->setStatusCode(200);
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
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
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
            ]);
    }
}
