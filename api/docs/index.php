<?php
header('Content-Type: application/json');

$swaggerFile = __DIR__ . '/../../swagger.json';
echo file_get_contents($swaggerFile);
