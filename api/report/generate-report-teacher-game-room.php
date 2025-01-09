<?php
require_once 'controllers/ReporteController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();

$name = $payload['name'];
$last_name = $payload['last_name'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    ReporteController::reportTeacher($name, $last_name);
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Método no permitido']);
}
