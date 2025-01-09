<?php
// bootstrap.php

// Definir la ruta base del proyecto
define('BASE_PATH', __DIR__);

// Autoloader para las clases
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Cargar variables de entorno
require_once BASE_PATH . '/config/Environment.php';
Environment::loadEnv(BASE_PATH . '/.env');