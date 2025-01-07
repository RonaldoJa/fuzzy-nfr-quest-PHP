<?php
require_once 'services/StudentService.php';

class StudentStatsController
{
    public static function getStudentGameHistory($email)
    {
        $attempts = StudentService::getStudentGameHistory($email);
        http_response_code(200);
        echo json_encode($attempts);
    }
}
