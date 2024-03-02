<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DoctorModel;
use App\Models\DoctorNumberModel;
use App\Utils\Utils;
use Firebase\JWT\JWT;

class DoctorController extends BaseController
{
    public function index($id)
    {
        $doctorModel = new DoctorModel();

        return $this->response
            ->setContentType("application/json")
            ->setJSON($doctorModel->find($id));
    }

    public function add_contact()
    {
        // TODO: Add to routes
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
        // TODO: Add to routes
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

    public function get_numbers()
    {
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
        $doctorModel = new DoctorModel();
        $post = $this->request->getJSON();

        if (
            !isset($post->name) ||
            !isset($post->email) ||
            !isset($post->password)
        ) {
            return $this->response
                ->setContentType('application/json')
                ->setJSON(['message' => 'invalid data shape'])
                ->setStatusCode(404);
        }

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
