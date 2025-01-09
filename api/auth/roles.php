<?php
require_once 'controllers/AuthController.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    AuthController::getRoles();
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Método no permitido']);
}
