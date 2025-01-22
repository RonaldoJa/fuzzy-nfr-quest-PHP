<?php
require_once 'controllers/GameController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    GameController::getParticipatingPlayersByGameRoom();
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Método no permitido']);
}
