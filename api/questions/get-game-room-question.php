<?php
require_once 'controllers/QuestionsController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();

$user_id = $payload['id'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    QuestionsController::getQuestionForRoomIdAndQuestionId($user_id);
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Método no permitido']);
}
