<?php

require_once 'helpers/globalHelper.php';
require_once 'services/UserService.php';

class UsersController
{
    public static function getUsers($user_id)
    {
        try {

            $isAdmin = UserService::isAdmin($user_id);

            if (!$isAdmin) {
                return GlobalHelper::generalResponse(null, 'Acceso denegado. Solo los administradores pueden acceder a este servicio.', 403);
            }

            $users = UserService::getUsers();
            return GlobalHelper::generalResponse($users, 'Lista de usuarios', 200);
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }

    public static function editUser($user_id)
    {
        try {
            // $isAdmin = UserService::isAdmin($user_id);

            // if (!$isAdmin) {
            //     return GlobalHelper::generalResponse(null, 'Acceso denegado. Solo los administradores pueden acceder a este servicio.', 403);
            // }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data) || !isset($data['user_id']) || !isset($data['role_id'])) {
                return GlobalHelper::generalResponse(null, 'Todos los campos son obligatorios y deben incluir id de usuario y rol.', 400);
            }

            $id = trim($data['user_id']);
            $role_id = trim($data['role_id']);

            if (empty($id) || empty($role_id)) {
                return GlobalHelper::generalResponse(null, 'Todos los campos son obligatorios y no pueden estar vacíos: id de usuario y rol.', 400);
            }

            $userExists = UserService::getById($id);

            if (!$userExists) {
                return GlobalHelper::generalResponse(null, 'El usuario no existe.', 400);
            }

            $roleExists = UserService::getByRol($role_id);

            if (!$roleExists) {
                return GlobalHelper::generalResponse(null, 'El rol ingresado no existe.', 404);
            }

            $userEdit = UserService::editUser($id, $role_id);

            if ($userEdit == 0) {
                return GlobalHelper::generalResponse(null, 'No se pudo actualizar el usuario.', 500);
            } else {
                return GlobalHelper::generalResponse(null, 'Usuario actualizado correctamente.', 200);
            }
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }
}
