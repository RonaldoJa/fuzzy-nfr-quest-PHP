<?php

require_once 'services/AdminService.php';

class AdminStatsController
{
    public static function getStatsByCourse($email)
    {
        $courseId = $_GET['params'][0];

        $stats = AdminService::getStatsByCourse($email, $courseId);
        http_response_code(200);
        echo json_encode($stats);
    }

    public static function getGeneratedRequirementsByCourse($email)
    {
        $courseId = $_GET['params'][0];

        $requirements = AdminService::getGeneratedRequirementsByCourse($email, $courseId);
        http_response_code(200);
        echo json_encode($requirements);
    }

    public static function editRequirement($email)
    {
        $requirementId = $_GET['params'][0];
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['text']) || empty($data['feedback']) || !isset($data['isValid'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionaron los datos necesarios']);
            exit;
        }

        $requirement = new RequirementEntity($requirementId, $data['text'], $data['isValid'], $data['feedback']);
        
        AdminService::editRequirement($email, $requirement);
        http_response_code(200);
        echo json_encode(['message' => 'Requisito editado con éxito']);
    }
}
