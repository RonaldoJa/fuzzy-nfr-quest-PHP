<?php
require_once 'services/JWTService.php';
require_once 'helpers/globalHelper.php';

class AuthMiddleware
{
    public static function validateToken()
    {
        try {
            $headers = getallheaders();

            $authHeader = $headers['Authorization'] ?? ($_SERVER['REDIRECT_REDIRECT_HTTP_AUTHORIZATION'] ?? null);

            if (empty($authHeader)) {
                GlobalHelper::generalResponse(null, 'Token no proporcionado', 401);
                exit();
            }

            $parts = explode(' ', $authHeader);

            if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
                GlobalHelper::generalResponse(null, 'Formato de token inválido', 400);
                exit();
            }

            $token = $parts[1];

            $payload = JWTService::verifyJWT($token);

            if (!$payload) {
                GlobalHelper::generalResponse(null, 'Token inválido o expirado', 401);
                exit();
            }

            return $payload;
        } catch (Exception $e) {
            GlobalHelper::generalResponse(null, 'Error interno del servidor: ' . $e->getMessage(), 500);
            exit();
        }
    }
}
