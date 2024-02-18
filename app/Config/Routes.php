<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group("", ["filter" => "ValidateJWTFilter"], function ($routes) {
    $routes->get("/patient/get/(:num)", "PatientController::index/$1");
    // NOTE: (:num)/(:num) refers to (patient_web_id)/(record_count) 
    $routes->get("/patient/record/(:num)/(:num)", "PatientController::get_recent_records/$1/$2");
    $routes->get("/doctor/get/(:num)", "DoctorController::index/$1");

    $routes->post("/patient/create", "PatientController::create_patient");
    $routes->post("/patient/record/upload", "PatientController::upload_record");
    $routes->post("/patient/assign", "PatientController::assign_doctor");
    $routes->post("/patient/sync", "PatientController::sync_patient_state");
    $routes->post("/patient/revoke", "DoctorController::revoke_doctor");

    $routes->post("/doctor/contact/get", "DoctorController::get_numbers");

    $routes->post("/doctor/create", "DoctorController::create_doctor");
    $routes->post("/doctor/login", "DoctorController::login");
    $routes->post("/doctor/contact/delete", "DoctorController::delete_contact");
    $routes->post("/doctor/contact/add", "DoctorController::add_contact");
});
