<?php
require_once 'config/Database.php';
require_once 'services/CourseService.php';

class GameService
{
    public static function prepareGameContent($courseId)
    {
        $course = CourseService::getById($courseId);

        if ($course === null) {
            http_response_code(404);
            echo json_encode(['message' => 'Curso no encontrado']);
            exit;
        }

        $items_per_attempt = $course['items_per_attempt'];

        $sql = "SELECT * FROM requirements WHERE course_id = :course_id ORDER BY RAND() LIMIT $items_per_attempt";
        $stmt = Database::getConn()->prepare($sql);
        $stmt->bindParam(':course_id', $course['id']);
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

        http_response_code(200);
        echo json_encode($formattedRequirements);
    }
}
