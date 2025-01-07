<?php

require_once 'services/UserService.php';
require_once 'services/CourseService.php';
require_once 'services/AttemptService.php';
require_once 'entities/RequirementEntity.php';
require_once 'config/Database.php';

class AdminService
{
    public static function getStatsByCourse($adminEmail, $courseId)
    {
        self::checkIfAdmin($adminEmail);
        self::checkIfCourseExists($courseId);

        $scoreAverage = self::getScoreAverageByCourse($courseId);
        $timeAverage = self::getTimeAverageByCourse($courseId);
        $dropoutRate = self::getDropoutRateByCourse($courseId);
        $gradesDistribution = self::getGradesDistribution($courseId);
        $totalAttempts = self::getTotalAttemptsByCourse($courseId);

        $stats = [
            'score_average' => $scoreAverage,
            'time_average' => $timeAverage,
            'dropout_rate' => $dropoutRate,
            'grades_distribution' => $gradesDistribution,
            'total_attempts' => $totalAttempts
        ];

        return $stats;
    }

    private static function getScoreAverageByCourse($courseId)
    {
        $query = "SELECT 
            AVG(a.score) AS avg_score
        FROM 
            courses c
        JOIN 
            attempts a ON c.id = a.course_id
        WHERE 
            a.status = 'completed'
            AND c.id = :course_id
        ";

        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();

        $avgScore = $stmt->fetchColumn();

        return round((float)$avgScore, 2);
    }

    private static function getTimeAverageByCourse($courseId)
    {
        $query = "SELECT 
            AVG(a.time) AS avg_time_seconds
        FROM 
            courses c
        JOIN 
            attempts a ON c.id = a.course_id
        WHERE 
            a.status = 'completed'
            AND c.id = :course_id
        ";

        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();

        $avgTime = $stmt->fetchColumn();

        return round((float)$avgTime, 2);
    }

    private static function getDropoutRateByCourse($courseId)
    {
        $query = "SELECT 
            (SUM(CASE WHEN a.status = 'abandoned' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)) AS abandonment_rate
        FROM 
            courses c
        JOIN 
            attempts a ON c.id = a.course_id
        WHERE
            c.id = :course_id
        ";

        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();

        $dropoutRate = $stmt->fetchColumn();

        return round((float)$dropoutRate, 2);
    }

    private static function getGradesDistribution($courseId)
    {
        $query = "WITH ranges AS (
            SELECT 0 AS range_start UNION ALL
            SELECT 1 UNION ALL
            SELECT 2 UNION ALL
            SELECT 3 UNION ALL
            SELECT 4 UNION ALL
            SELECT 5 UNION ALL
            SELECT 6 UNION ALL
            SELECT 7 UNION ALL
            SELECT 8 UNION ALL
            SELECT 9
        )
        SELECT 
            CONCAT(r.range_start, '-', r.range_start + 1) AS score_range,
            IFNULL(COUNT(a.id), 0) AS count
        FROM 
            ranges r
        LEFT JOIN 
            attempts a 
            ON (CASE 
                    WHEN a.score = 1.0 THEN 9 -- Puntajes perfectos (100%) asignados al rango 9-10
                    ELSE FLOOR(a.score * 10) 
                END) = r.range_start
            AND a.course_id = :course_id
            AND a.status = 'completed'
        GROUP BY 
            r.range_start
        ORDER BY 
            r.range_start";

        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();

        $gradesDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $gradesDistribution;
    }

    private static function getTotalAttemptsByCourse($courseId)
    {
        $query = "SELECT 
            COUNT(a.id) AS total_attempts
        FROM 
            courses c
        JOIN 
            attempts a ON c.id = a.course_id
        WHERE 
            c.id = :course_id";

        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public static function getGeneratedRequirementsByCourse($adminEmail, $courseId)
    {
        self::checkIfAdmin($adminEmail);
        self::checkIfCourseExists($courseId);

        $query = "SELECT * FROM requirements WHERE course_id = :course_id";

        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':course_id', $courseId);
        $stmt->execute();

        $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedRequirements = [];

        foreach ($requirements as $requirement) {
            $formattedRequirements[] = [
                'id' => $requirement['id'],
                'text' => $requirement['requirementText'],
                'isValid' => $requirement['isValid'] == 1 ? true : false,
                'feedback' => $requirement['feedbackText'],
            ];
        }

        return $formattedRequirements;
    }

    public static function editRequirement($adminEmail, RequirementEntity $requirement)
    {
        self::checkIfAdmin($adminEmail);
        self::checkIfRequirementExists($requirement->id);
        
        $query = "UPDATE requirements SET requirementText = :requirementText, isValid = :isValid, feedbackText = :feedbackText WHERE id = :requirement_id";

        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':requirement_id', $requirement->id);
        $stmt->bindParam(':requirementText', $requirement->text);
        $stmt->bindParam(':isValid', $requirement->isValid, PDO::PARAM_INT);
        $stmt->bindParam(':feedbackText', $requirement->feedback);

        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['message' => 'Error al editar el requisito']);
            exit;
        }

        return true;
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

    private static function checkIfRequirementExists($requirementId)
    {
        $query = "SELECT * FROM requirements WHERE id = :requirement_id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':requirement_id', $requirementId);
        $stmt->execute();

        $requirementExists = $stmt->fetchColumn();

        if (!$requirementExists) {
            http_response_code(404);
            echo json_encode(['message' => 'Requisito no encontrado']);
            exit;
        }

        return $requirementExists;
    }

    private static function checkIfAdmin($adminEmail)
    {
        $isAdmin = UserService::isAdmin($adminEmail);
        if (!$isAdmin) {
            http_response_code(403);
            echo json_encode(['message' => 'Acceso denegado']);
            exit;
        }
        return $isAdmin;
    }
}
