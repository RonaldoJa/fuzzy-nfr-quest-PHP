<?php
require_once 'services/UserService.php';

class StudentService
{
    public static function getStudentGameHistory($email)
    {
        if (UserService::isAdmin($email)) {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden']);
            exit;
        }

        $user = UserService::getByEmail($email);

        $query = "SELECT * FROM attempts INNER JOIN courses ON attempts.course_id = courses.id WHERE attempts.user_id = :user_id ORDER BY attempts.created_at DESC";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user['id']);
        $stmt->execute();
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $attempts;
    }
}
