<?php
require_once 'controllers/AttemptController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();
$user_id = $payload['id'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        AttemptController::checkAttemptsRemaining($user_id);
        break;
    case 'POST':
        AttemptController::registerAttempt($user_id);
        break;
    case 'PUT':
        AttemptController::updateStatsAndStatus($user_id);
        break;
}
