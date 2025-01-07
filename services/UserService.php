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

    public static function isAdmin($email)
    {
        $user = self::getByEmail($email);
        if (is_null($user)) {
            return false;
        }
        return $user['role'] === 'admin';
    }

    public static function getInfo($user)
    {
        return [
            'id' => $user['id'],
            'email' => $user['email'],
            "first_name" => $user['first_name'],
            "last_name" => $user['last_name'],
            "role" => $user['role'],
        ];
    }

    public static function createUser(UserEntity $user)
    {
        $query = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (:first_name, :last_name, :email, :password, :role)";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':first_name', $user->firstName);
        $stmt->bindParam(':last_name', $user->lastName);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':password', $user->password);
        $stmt->bindParam(':role', $user->role);
        $stmt->execute();
        return Database::getConn()->lastInsertId();
    }

    public static function deleteAll()
    {
        $query = "DELETE FROM users";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute();
    }
}
