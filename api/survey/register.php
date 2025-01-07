<?php

require_once 'controllers/SurveyController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();
$userId = $payload['id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    SurveyController::markSurveySubmissionAsCompleted($userId);
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido.']);
}
