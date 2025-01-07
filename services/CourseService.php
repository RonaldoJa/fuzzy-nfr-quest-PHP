<?php

require_once 'entities/CourseEntity.php';
require_once 'entities/GameConfigEntity.php';
require_once 'config/Database.php';
require_once 'services/GptService.php';

class CourseService
{

    public static function create(CourseEntity $course, GameConfigEntity $gameConfig, $requirements, $user_id)
    {
        try {
            Database::getConn()->beginTransaction();

            $query = "INSERT INTO courses (course_name, course_code, items_per_attempt, max_attempts, user_id) VALUES (:course_name, :course_code, :items_per_attempt, :max_attempts, :user_id)";
            $stmt = Database::getConn()->prepare($query);
            $stmt->bindParam(':course_name', $course->course_name);
            $stmt->bindParam(':course_code', $course->course_code);
            $stmt->bindParam(':items_per_attempt', $course->items_per_attempt);
            $stmt->bindParam(':max_attempts', $course->max_attempts);
            $stmt->bindParam(':user_id', $user_id);

            if (!$stmt->execute()) {
                throw new Exception("Error al crear el curso en la base de datos.");
            }

            $courseId = Database::getConn()->lastInsertId();

            if ($course->content_mode === 'generated') {
                $response = GptService::generateRequirements($gameConfig);
                self::saveRequirements($courseId, $response['requirements']);
            }

            if ($course->content_mode === 'file_upload') {
                self::saveRequirements($courseId, $requirements);
            }

            Database::getConn()->commit();

            return self::getById($courseId);
        } catch (Exception $e) {
            Database::getConn()->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public static function getAllByUserId($user_id)
    {
        $query = "SELECT * FROM courses WHERE user_id = :user_id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function getAllEnrolledByUserId($user_id)
    {
        $query = "SELECT courses.id, courses.course_name, courses.course_code, courses.created_at, courses.max_attempts,
        courses.user_id AS teacher_id,
        users.email AS teacher_email, 
        CONCAT(users.first_name, ' ', users.last_name) AS teacher_name
                 FROM enrolled_courses 
                 JOIN courses ON enrolled_courses.course_id = courses.id
                 JOIN users ON courses.user_id = users.id
                 WHERE enrolled_courses.user_id = :user_id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function enroll($user_id, $courseId)
    {
        $query = "INSERT INTO enrolled_courses (user_id, course_id) VALUES (:user_id, :course_id)";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':course_id', $courseId);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public static function getByCode($course_code)
    {
        $query = "SELECT * FROM courses WHERE course_code = :course_code";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':course_code', $course_code);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public static function getById($id)
    {
        $query = "SELECT * FROM courses WHERE id = :id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public static function remove($id)
    {
        self::checkIfCourseExists($id);
        
        $query = "DELETE FROM courses WHERE id = :id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteAll()
    {
        $query = "DELETE FROM courses";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute();
    }

    public static function saveRequirements($courseId, $requirements)
    {
        try {
            foreach ($requirements as $requirement) {
                $query = "INSERT INTO requirements (requirementText, isValid, feedbackText, course_id) VALUES (:requirementText, :isValid, :feedbackText, :course_id)";
                $stmt = Database::getConn()->prepare($query);
                $requirementText = htmlspecialchars($requirement['text']);
                $feedbackText = htmlspecialchars($requirement['feedback']);
                $stmt->bindParam(':requirementText', $requirementText);
                $stmt->bindParam(':isValid', $requirement['isValid'], PDO::PARAM_INT);
                $stmt->bindParam(':feedbackText', $feedbackText);
                $stmt->bindParam(':course_id', $courseId);

                if (!$stmt->execute()) {
                    throw new Exception('Ocurrió un error al guardar los requisitos');
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private static function checkIfCourseExists($courseId)
    {
        $courseExists = CourseService::getById($courseId);
        if (!$courseExists) {
            http_response_code(404);
            echo json_encode(['message' => 'Curso no encontrado']);
            exit;
        }
        return $courseExists;
    }
}
