<?php
require_once 'controllers/AdminStatsController.php';
require_once 'middleware/AuthMiddleware.php';

$payload = AuthMiddleware::validateToken();
$email = $payload['email'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'PUT':
        AdminStatsController::editRequirement($email);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Método no permitido']);
        break;
}
?>