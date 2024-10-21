<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ChatConnectionModel;
use App\Models\ChatMessageModel;
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

  public function update_profile()
  {
    $post = $this->request->getJSON();

    if (
      !isset($post->doctor_id) ||
      !isset($post->name) ||
      !isset($post->email)
    ) {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'invalid data shape'])
        ->setStatusCode(404);
    }

    $doctorModel = new DoctorModel();
    $existingEmail = $doctorModel->where('email', $post->email)
      ->where('doctor_id != ' . $post->doctor_id)
      ->find();

    if (count($existingEmail) > 0) {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'email already exists'])
        ->setStatusCode(404);
    }

    $existingName = $doctorModel->where('name', $post->name)
      ->where('doctor_id != ' . $post->doctor_id)
      ->find();

    if (count($existingName) > 0) {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'name already exists'])
        ->setStatusCode(404);
    }

    $doctorModel->update($post->doctor_id, ['name' => $post->name, 'email' => $post->email]);
    return $this->response
      ->setContentType('application/json')
      ->setJSON(['message' => 'success'])
      ->setStatusCode(200);
  }

  public function get_profile_pic($filename)
  {
    $filepath = APPPATH . 'uploads/' . $filename;
    if (file_exists($filepath)) {
      return $this->response
        ->setContentType(mime_content_type($filepath))
        ->setHeader('Content-Length', filesize($filepath))
        ->setBody(file_get_contents($filepath));
    }

    return $this->response
      ->setStatusCode(404);
  }

  public function change_profile_pic()
  {
    $post = $this->request->getJSON();

    if (
      !isset($post->doctor_id) ||
      !isset($post->pic)
    ) {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'invalid data shape'])
        ->setStatusCode(404);
    }

    $uid = substr(sha1($post->doctor_id), 0, 16);
    $filepath = APPPATH . 'uploads/' . $uid;

    if (str_contains($post->pic, 'data:image/jpeg;base64,')) {
      $data = str_replace('data:image/jpeg;base64,', '', $post->pic);
      $path = $filepath . '.jpg';
      $link = $uid . '.jpg';
    } else if (str_contains($post->pic, 'data:image/png;base64,')) {
      $data = str_replace('data:image/png;base64,', '', $post->pic);
      $path = $filepath . '.png';
      $link = $uid . '.png';
    } else {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'invalid image'])
        ->setStatusCode(404);
    }

    file_put_contents($path, base64_decode($data));
    $doctorModel = new DoctorModel();
    $doctorModel->update($post->doctor_id, ['profile_picture_link' => '/dialife-api/doctor/profilepic/' . $link]);

    return $this->response
      ->setContentType("application/json")
      ->setJSON(['message' => 'Success', 'link' => '/dialife-api/doctor/profilepic/' . $link]);
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

  public function connect_chat()
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

    $chatConnectionModel = new ChatConnectionModel();
    $chatConnectionModel->insert([
      'fk_doctor_id' => $payload['id'],
      'fk_patient_id' => $post->patient_id
    ]);

    return $this->response
      ->setContentType('application/json')
      ->setJSON(['message' => 'Success'])
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

    $existingName = $doctorModel->where('name', $post->name)
      ->find();

    if (count($existingName) > 0) {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'name already exists'])
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

  public function get_connected_chats()
  {
    $jwt = get_cookie("jwt");

    if (!isset($jwt) || $jwt === "deleted") {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'Unauthorized'])
        ->setStatusCode(403);
    }

    $payload = Utils::parseJWT($jwt);

    $chatConnectionModel = new ChatConnectionModel();
    $connections = $chatConnectionModel->where('fk_doctor_id', $payload['id'])->findAll();

    $patients = array();
    foreach ($connections as $connection) {
      $patientModel = new PatientModel();
      $patient = $patientModel->find($connection['fk_patient_id']);
      array_push($patients, $patient);
    }

    return $this->response
      ->setContentType('application/json')
      ->setJSON($patients)
      ->setStatusCode(200);
  }

  public function get_chat_id($doctor_id, $patient_id)
  {
    $chatConnectionModel = new ChatConnectionModel();
    $connection = $chatConnectionModel->where('fk_doctor_id', $doctor_id)
      ->where('fk_patient_id', $patient_id)
      ->find();

    if (!isset($connection[0])) {
      return $this->response
        ->setContentType('application/json')
        ->setJSON(['message' => 'Connection Not Found'])
        ->setStatusCode(404);
    }

    return $this->response
      ->setContentType('application/json')
      ->setJSON($connection[0])
      ->setStatusCode(200);
  }

  public function send_message($chat_connection_id)
  {
    $post = $this->request->getJSON();

    $chatMessageModel = new ChatMessageModel();
    $chatMessageModel->insert([
      'fk_chat_connection_id' => $chat_connection_id,
      'sender_type' => $post->sender_type,
      'sender_id' => $post->sender_id,
      'content' => $post->content
    ]);

    return $this->response
      ->setContentType('application/json')
      ->setJSON(['message' => 'Success'])
      ->setStatusCode(200);
  }

  public function get_messages($chat_connection_id)
  {
    $chatMessageModel = new ChatMessageModel();
    $messages = $chatMessageModel->where('fk_chat_connection_id', $chat_connection_id)->findAll();

    return $this->response
      ->setContentType('application/json')
      ->setJSON($messages)
      ->setStatusCode(200);
  }
}
