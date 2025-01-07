<?php

require_once 'controllers/SeedController.php';

// Definir usuario y contraseña (en producción, usa variables de entorno)
define('SEED_USER', getenv('SEED_USER'));
define('SEED_PASSWORD', getenv('SEED_PASSWORD'));

// Verificar las credenciales de autenticación
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== SEED_USER || $_SERVER['PHP_AUTH_PW'] !== SEED_PASSWORD) {
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header("HTTP/1.0 401 No autorizado");
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar si el entorno es de producción
if (getenv('APP_ENV') === 'production') {
    echo json_encode(['error' => 'No se puede ejecutar el seed en producción']);
    exit;
}

// Si las credenciales son correctas y no es producción, ejecuta el seed
try {
    SeedController::seed();
    echo json_encode(['message' => 'Seed ejecutado con éxito']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al ejecutar el seed: ' . $e->getMessage()]);
}
