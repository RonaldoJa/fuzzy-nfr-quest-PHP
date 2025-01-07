<?php
require_once 'config/Database.php';
require_once 'services/CourseService.php';
class AttemptService {

    public static function registerAttempt($user_id, $courseId, $totalreq) {

        $course = CourseService::getById($courseId);

        if (!$course) {
            http_response_code(404);
            echo json_encode(['message' => 'Curso no encontrado.']);
            return;
        }

        $hasAttemptsRemaining = self::checkAttemptsRemaining($user_id, $courseId);
        if (!$hasAttemptsRemaining['remaining']) {
            http_response_code(400);
            echo json_encode(['message' => 'No tienes más intentos disponibles para esta sala.']);
            return;
        }

        $query = "INSERT INTO attempts (user_id, course_id, totalreq) VALUES (:user_id, :course_id, :totalreq)";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':course_id', $course['id']);
        $stmt->bindParam(':totalreq', $totalreq);

        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['message' => 'Error al registrar el intento.']);
            return;
        }
        
        $attemptId = (int) Database::getConn()->lastInsertId();
        http_response_code(201);
        echo json_encode(['id' => $attemptId]);
    }

    public static function checkAttemptsRemaining($user_id, $courseId) {
        $course = CourseService::getById($courseId);
        
        if (!$course) {
            http_response_code(404);
            echo json_encode(['message' => 'Curso no encontrado.']);
            return;
        }

        $query = "SELECT COUNT(*) FROM attempts WHERE user_id = :user_id AND course_id = :course_id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':course_id', $course['id']);
        $stmt->execute();
        $attempts = $stmt->fetchColumn();
        return [
            'remaining' => $course['max_attempts'] - $attempts,
            'max_attempts' => $course['max_attempts']
        ];
    }

    public static function updateStatsAndStatus($attemptId, $status, $score, $movements, $time) {
        self::getAttemptById($attemptId);

        $query = "UPDATE attempts SET status = :status, score = :score, movements = :movements, time = :time WHERE id = :attemptId";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':attemptId', $attemptId);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':movements', $movements);
        $stmt->bindParam(':time', $time);

        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['message' => 'Error al actualizar las estadisticas y el estado del intento.']);
            return;
        }

        http_response_code(200);
        echo json_encode(['message' => 'Estadisticas y estado actualizados exitosamente.']);
    }

    public static function getAttemptById($attemptId) {
        $query = "SELECT * FROM attempts WHERE id = :attemptId";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':attemptId', $attemptId);
        $stmt->execute();
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attempt) {
            http_response_code(404);
            echo json_encode(['message' => 'Intento no encontrado.']);
            return null;
        }

        return $attempt;
    }

}