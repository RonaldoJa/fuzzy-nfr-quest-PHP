<?php
require_once 'middleware/AuthMiddleware.php';
require_once 'controllers/CourseController.php';

$method = $_SERVER['REQUEST_METHOD'];

$payload = AuthMiddleware::validateToken();

$user_id = $payload['id'];
$email = $payload['email'];

switch ($method) {
    case 'GET':
        CourseController::getAllCourses($user_id, $email);
        break;
    case 'POST':
        CourseController::createCourse($user_id, $email);
        break;
    case 'DELETE':
        CourseController::deleteCourse($user_id, $email);
        break;
}
