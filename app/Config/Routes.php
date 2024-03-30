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
    $routes->get("/doctor/of/(:num)", "DoctorController::get_connected_patients/$1");

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

    $routes->post("/doctor/create", "DoctorController::create_doctor");
    $routes->post("/doctor/login", "DoctorController::login");
    $routes->post("/doctor/contact/delete", "DoctorController::delete_contact");
    $routes->post("/doctor/contact/add", "DoctorController::add_contact");
    $routes->post("/generate/key/", "DoctorController::gen_registration_keys");
});
