<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group("", ["filter" => "ValidateJWTFilter"], function ($routes) {
    $routes->get("/patient/get/(:num)", "PatientController::index/$1");
    $routes->get("/patient/get/all", "PatientController::all");
    // NOTE: (:num)/(:num) refers to (patient_web_id)/(record_count) 
    $routes->get("/patient/record/(:num)/(:num)", "PatientController::get_recent_records/$1/$2");
    $routes->get("/doctor/get/(:num)", "DoctorController::index/$1");

    $routes->get("/admin/get/(:num)", "DoctorController::get_admin/$1");
    $routes->get("/admin/regkeys", "DoctorController::get_all_regkeys");
    $routes->get("/admin/keyusages", "DoctorController::get_key_usages");

    $routes->get("/doctor/of/(:num)", "DoctorController::get_connected_patients/$1");
    $routes->get("/patient/doctors/get/(:num)", "PatientController::get_doctors/$1");

    $routes->get("/doctor/profilepic/(:segment)", "DoctorController::get_profile_pic/$1");
    $routes->get("/doctor/chat/connected", "DoctorController::get_connected_chats");
    $routes->get("/doctor/chat/getid/(:num)/(:num)", "DoctorController::get_chat_id/$1/$2");

    $routes->post("/message/send/(:num)", "DoctorController::send_message/$1");
    $routes->get("/message/get/(:num)", "DoctorController::get_messages/$1");

    $routes->post("/patient/record/latest", "PatientController::get_latest_record");
    $routes->post("/patient/record/consolidated", "PatientController::get_records");
    $routes->post("/patient/create", "PatientController::create_patient");
    $routes->post("/patient/record/upload", "PatientController::upload_record");
    $routes->post("/patient/assign", "PatientController::assign_doctor");
    $routes->post("/patient/sync", "PatientController::sync_patient_state");
    $routes->post("/patient/revoke", "PatientController::revoke_doctor");
    $routes->post("/patient/record/syncall", "PatientController::sync_all_records");
    
    $routes->post("/doctor/checkauth", "DoctorController::check_auth");
    $routes->post("/doctor/contact/get", "DoctorController::get_numbers");
    $routes->post("/doctor/chat/initiate", "DoctorController::connect_chat");

    $routes->post("/doctor/create", "DoctorController::create_doctor");
    $routes->post("/doctor/login", "DoctorController::login");
    $routes->post("/doctor/contact/delete", "DoctorController::delete_contact");
    $routes->post("/doctor/contact/add", "DoctorController::add_contact");
    $routes->post("/doctor/profilepic", "DoctorController::change_profile_pic");
    $routes->post("/doctor/profile", "DoctorController::update_profile");

    $routes->post("/admin/generate/key/", "DoctorController::gen_registration_keys");
});
