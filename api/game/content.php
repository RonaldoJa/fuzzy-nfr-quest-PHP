<?php

require_once 'controllers/GameController.php';
require_once 'middleware/AuthMiddleware.php';

AuthMiddleware::validateToken();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    GameController::prepareGameContent();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido.']);
}