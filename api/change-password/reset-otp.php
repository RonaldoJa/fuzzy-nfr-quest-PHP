<?php
require_once 'controllers/ChangePasswordController.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    ChangePasswordController::resetPassword();
} else {
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode(['error' => 'Método no permitido']);
}
