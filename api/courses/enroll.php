<?php
require_once 'middleware/AuthMiddleware.php';
require_once 'controllers/CourseController.php';

$payload = AuthMiddleware::validateToken();

CourseController::enroll($payload['id'], $payload['email']);
