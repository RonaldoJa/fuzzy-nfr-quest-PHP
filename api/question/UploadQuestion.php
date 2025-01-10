<?php
require_once 'controllers/QuestionController.php';
require_once 'middleware/AuthMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];

$payload = AuthMiddleware::validateToken();

$user_id = $payload['id'];
$email = $payload['email'];

if ($method === 'POST') {
    QuestionController::InsertQuestions($user_id);
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Método no permitido']);
}
