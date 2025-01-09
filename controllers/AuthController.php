<?php
require_once 'services/JWTService.php';
require_once 'config/Database.php';
require_once 'services/UserService.php';
require_once 'helpers/globalHelper.php';

class AuthController
{
    public static function getRoles()
    {
        try {
            $query = "select id, name, description from roles where status = 1";
            $stmt = Database::getConn()->prepare($query);
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return GlobalHelper::generalResponse($roles, 'Proceso exitoso.');
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }

    public static function register()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $name = trim($data['name']) ?? null;
            $last_name = trim($data['last_name']) ?? null;
            $username = trim($data['username']) ?? null;
            $email = trim($data['email']) ?? null;
            $birth_date = trim($data['birth_date']) ?? null;
            $password = trim($data['password']) ?? null;
            $role = trim($data['role']) ?? null;


            if (empty($name) || empty($last_name) || empty($username) || empty($email) || empty($birth_date) || empty($password) || empty($role)) {
                return GlobalHelper::generalResponse(null, 'Todos los campos son obligatorios y no pueden estar vacíos: nombres, apellidos, correo electrónico, contraseña y rol', 400);
            }

            if (strlen($password) <= 5) {
                return GlobalHelper::generalResponse(null, 'La contraseña debe tener más de 5 caracteres.', 400);
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT) ?? null;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return GlobalHelper::generalResponse(null, 'El correo electrónico no es válido.', 400);
            }

            $roleExists = UserService::getByRol($role);

            if (!$roleExists) {
                return GlobalHelper::generalResponse(null, 'El rol ingresado no existe.', 404);
            }

            $userExists = UserService::getByEmail($email);

            if ($userExists) {
                return GlobalHelper::generalResponse(null, 'El usuario ingresado ya existe.', 400);
            }

            $query = "INSERT INTO users (`name`, last_name, username, email, birth_date, `password`, role_id) VALUES (:name, :last_name, :username, :email, :birth_date, :password, :role_id)";
            $stmt = Database::getConn()->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':birth_date', $birth_date);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':role_id', $role);

            if ($stmt->execute()) {
                return GlobalHelper::generalResponse(null, 'Usuario registrado con éxito', 201);
            } else {
                return GlobalHelper::generalResponse(null, 'Ocurrio un error al registrar, intente mas tarde.', 500);
            }
        } catch (\Throwable $th) {

            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }

    public static function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'];
        $password = $data['password'];

        $user = UserService::getByEmail($email);

        if (!$user) {
            return GlobalHelper::generalResponse(null, 'Correo electrónico o contraseña no válidos', 401);
        }

        if (!password_verify($password, $user['password']) || !$user) {
            return GlobalHelper::generalResponse(null, 'Correo electrónico o contraseña no válidos', 401);
        }

        $response = self::generateLoginResponse($user);
        return GlobalHelper::generalResponse($response, 'Proceso exitoso.');
    }

    private static function generateLoginResponse($user)
    {
        $token = JWTService::generateJWT($user['id'], $user['email'], $user['name'], $user['last_name']);

        return  [
            'access_token' => $token,
            'user' => UserService::getInfo($user),
        ];
    }
}
