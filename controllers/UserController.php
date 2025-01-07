<?php
require_once 'config/Database.php';
require_once 'services/UserService.php';

class UserController
{
    public static function getAllUsers()
    {
        $query = "SELECT * FROM users";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute();
        $usersFromDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];

        foreach ($usersFromDB as $user) {
            $users[] = UserService::getInfo($user);
        }

        http_response_code(200);
        echo json_encode($users);
    }
}
