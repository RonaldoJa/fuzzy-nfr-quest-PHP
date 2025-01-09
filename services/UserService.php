<?php
require_once 'config/Database.php';

class UserService
{
    public static function getByEmail($email)
    {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public static function getByRol($role_id)
    {
        $query = "SELECT id, name, description FROM roles WHERE id = :role_id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public static function resetPassword($email, $password)
    {
        $query = "UPDATE users SET password = :password, updated_at = now() WHERE email = :email";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public static function isTeacher($user_id)
    {
        $query = "SELECT r.name from users u inner join roles r on u.role_id  = r.id where u.id = :user_id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }

        $user = $result;
        
        if (is_null($user)) {
            return false;
        }
        return $user['name'] === 'Docente';
    }

    public static function getInfo($user)
    {
        return [
            'id' => $user['id'],
            'email' => $user['email'],
            "name" => $user['name'],
            "last_name" => $user['last_name'],
            "role" => self::getByRol($user["role_id"])
        ];
    }
}
