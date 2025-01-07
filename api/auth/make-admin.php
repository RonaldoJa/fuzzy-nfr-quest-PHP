<?php

require_once 'controllers/AuthController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();

$user_id = $payload['id'];
$email = $payload['email'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'PUT') {
    AuthController::makeAdmin($email);
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Método no permitido']);
}
