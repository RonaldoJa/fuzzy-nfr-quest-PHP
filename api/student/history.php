<?php
require_once 'controllers/StudentStatsController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();

$email = $payload['email'];

StudentStatsController::getStudentGameHistory($email);
