<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConnectionModel;
use App\Models\DoctorModel;
use App\Models\DoctorNumberModel;
use App\Models\PatientModel;
use App\Models\RegistrationKeyModel;
use App\Utils\Utils;
use Firebase\JWT\JWT;

class DoctorController extends BaseController
{
    public function index($id)
    {
        $doctorModel = new DoctorModel();
        $doctor = $doctorModel->find($id);
        unset($doctor['password']);

        return $this->response
            ->setContentType("application/json")
            ->setJSON($doctor);
    }

    public function gen_registration_keys()
    {
        $post = $this->request->getJSON();

        if (
            !isset($post->count) ||
            !isset($post->secret)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        $keys = array();
        for ($i = 0; $i < $post->count; $i++) {
            $key = implode('-', str_split(substr(strtolower(md5(microtime() . rand(1000, 9999))), 0, 30), 6));
            array_push($keys, ['key_string' => $key]);
        }

        $keyModel = new RegistrationKeyModel();
        $keyModel->insertBatch($keys);

        return $this->response
            ->setContentType('application/json')
            ->setJSON($keys);
    }

    public function check_auth()
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        return $this->response->setStatusCode(200);
    }

    public function add_contact()
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $payload = Utils::parseJWT($jwt);
        $post = $this->request->getJSON();

        if (
            !isset($post->doctor_id) ||
            !isset($post->number)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        if ($payload['id'] != $post->doctor_id) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $numberModel = new DoctorNumberModel();
        $numberModel->insert([
            'fk_doctor_id' => $post->doctor_id,
            'number' => $post->number
        ]);

        return $this->response
            ->setContentType('application/json')
            ->setJSON(['message' => 'Success'])
            ->setStatusCode(200);
    }

    public function delete_contact()
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $payload = Utils::parseJWT($jwt);
        $post = $this->request->getJSON();

        if (
            !isset($post->doctor_id) ||
            !isset($post->number)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        if ($payload['id'] != $post->doctor_id) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $numberModel = new DoctorNumberModel();
        $numberModel->where('number', $post->number)->delete();

        // SUGGESTION: Maybe check for existence of number

        return $this->response
            ->setContentType('application/json')
            ->setJSON(['message' => 'Success'])
            ->setStatusCode(200);
    }

    public function get_connected_patients($doctorId)
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $connectionModel = new ConnectionModel();
        $connections = $connectionModel->where("fk_doctor_id", $doctorId)->findAll();

        $patients = array();
        foreach ($connections as $connection) {
            $patientModel = new PatientModel();
            $patient = $patientModel->find($connection['fk_patient_id']);
            array_push($patients, $patient);
        }

        return $this->response
            ->setContentType('application/json')
            ->setJSON($patients);
    }

    public function get_numbers()
    {
        $jwt = get_cookie("jwt");

        if (!isset($jwt) || $jwt === "deleted") {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }
        // TODO: Add to routes
        // TODO: Check for patient doctor connection
        $post = $this->request->getJSON();

        // TODO: Add secret key from app
        if (
            !isset($post->patient_web_id) ||
            !isset($post->doctor_id)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        $numberModel = new DoctorNumberModel();
        $numbers = $numberModel
            ->where('fk_doctor_id', $post->doctor_id)
            ->findAll();

        return $this->response
            ->setContentType('application/json')
            ->setJSON($numbers);
    }

    public function login()
    {
        $doctorModel = new DoctorModel();
        $post = $this->request->getJSON();

        if (
            !isset($post->email) ||
            !isset($post->password)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        $doctors = $doctorModel->where("email", $post->email)->findAll();
        if (empty($doctors)) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Wrong email or password'])
                ->setStatusCode(200);
        }
        $doctor = $doctors[0];

        if (!password_verify($post->password, $doctor["password"])) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Wrong email or password'])
                ->setStatusCode(200);
        }

        $payload = [
            'id' => $doctor['doctor_id'],
            'iss' => 'https://dialife.io',
            // NOTE: time() + x, where the JWT expires in 'x' seconds 
            'exp' => time() + 3600,
        ];

        $jwt = JWT::encode($payload, getenv("SECRET_KEY"), 'HS256');
        setcookie("jwt", $jwt, path: '/');

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'message' => 'Success',
                'id' => $doctor['doctor_id'],
            ])
            ->setStatusCode(200);
    }

    public function create_doctor()
    {
        $post = $this->request->getJSON();

        if (
            !isset($post->name) ||
            !isset($post->email) ||
            !isset($post->password) ||
            !isset($post->regkey)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

        $keyModel = new RegistrationKeyModel();
        $key = $keyModel->where('key_string', $post->regkey)
            ->find();

        if (count($key) < 1 || (int)$key[0]['used']) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(403);
        }

        $doctorModel = new DoctorModel();
        $existingEmail = $doctorModel->where('email', $post->email)
            ->find();

        if (count($existingEmail) > 0) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'email already exists'])
                ->setStatusCode(404);
        }

        $key = $keyModel->where('key_string', $post->regkey)->find(); 
        $keyModel->update($key[0]['registration_key_id'], ['used' => true]);

        $doctorModel->insert([
            'name' => $post->name,
            'email' => $post->email,
            'password' => password_hash($post->password, PASSWORD_ARGON2I),
        ]);

        return $this->response
            ->setContentType('application/json')
            ->setJSON(['message' => 'Success'])
            ->setStatusCode(404);
    }
}
