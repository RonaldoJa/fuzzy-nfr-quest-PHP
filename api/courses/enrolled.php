<?php
require_once 'middleware/AuthMiddleware.php';
require_once 'controllers/CourseController.php';

$payload = AuthMiddleware::validateToken();

CourseController::getEnrolledCourses($payload['id'], $payload['email']);