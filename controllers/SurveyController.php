<?php
require_once 'services/SurveyService.php';
require_once 'entities/ResponseEntity.php';

class SurveyController
{
    public static function markSurveySubmissionAsCompleted($userId)
    {
        SurveyService::markSurveySubmissionAsCompleted($userId);
        http_response_code(200);
        echo json_encode(['message' => 'Encuesta registrada correctamente.']);
    }

    public static function getSurveyQuestions()
    {
        $questions = SurveyService::getSurveyQuestions();
        http_response_code(200);
        echo json_encode($questions);
    }

    public static function saveSurveyResponses($userId)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['responses']) || !is_array($data['responses'])) {
            http_response_code(400);
            echo json_encode(['message' => 'El formato de las respuestas es inválido.']);
            exit;
        }

        $rawResponses = $data['responses'];
        $responses = array_map(function ($response) use ($userId) {
            if (!isset($response['surveyQuestionId']) || !isset($response['response'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Una o más respuestas tienen un formato inválido.']);
                exit;
            }
            if ($response['response'] < 1 || $response['response'] > 5) {
                http_response_code(400);
                echo json_encode(['message' => 'Una o más respuestas tienen un valor inválido.']);
                exit;
            }
            return new ResponseEntity($userId, $response['surveyQuestionId'], $response['response']);
        }, $rawResponses);

        SurveyService::saveSurveyResponses($userId, $responses);
        http_response_code(200);
        echo json_encode(['message' => 'Respuestas de la encuesta registradas correctamente.']);
    }
}
