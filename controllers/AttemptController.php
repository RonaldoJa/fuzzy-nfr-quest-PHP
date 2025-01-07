<?php
require_once 'services/AttemptService.php';

class AttemptController {

    public static function checkAttemptsRemaining($user_id) {
        $courseId = $_GET['params'][0];
        
        $availableAttempts = AttemptService::checkAttemptsRemaining($user_id, $courseId);
        http_response_code(200);
        echo json_encode($availableAttempts);
    }

    public static function registerAttempt($user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $courseId = $data['course_id'] ?? null;
        $totalreq = $data['totalreq'] ?? null;

        AttemptService::registerAttempt($user_id, $courseId, $totalreq);
    }

    public static function updateStatsAndStatus($user_id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $attemptId = $data['attemptId'] ?? null;
        $status = $data['status'] ?? null;
        $score = $data['score'] ?? null;
        $movements = $data['movements'] ?? null;
        $time = $data['time'] ?? null;

        AttemptService::updateStatsAndStatus($attemptId, $status, $score, $movements, $time);
    }
}