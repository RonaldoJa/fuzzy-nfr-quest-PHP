<?php

require_once 'config/Database.php';

class SurveyService
{
    public static function markSurveySubmissionAsCompleted($userId)
    {
        self::checkIfUserHasAlreadySubmittedSurvey($userId);
        
        $query = "INSERT INTO survey_submissions (user_id) VALUES (:userId)";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':userId', $userId);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['message' => 'Error al registrar la respuesta de la encuesta.']);
            exit;
        }

        return true;
    }

    public static function checkIfUserHasAlreadySubmittedSurvey($userId)
    {
        $query = "SELECT * FROM survey_submissions WHERE user_id = :userId";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $userHasAlreadySubmittedSurvey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userHasAlreadySubmittedSurvey) {
            http_response_code(400);
            echo json_encode(['message' => 'El usuario ya ha realizado la encuesta.']);
            exit;
        }

        return $userHasAlreadySubmittedSurvey;
    }

    public static function getSurveyQuestions()
    {
        $query = "SELECT * FROM survey_questions";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function saveSurveyResponses($userId, array $responses)
    {
        Database::getConn()->beginTransaction();
        try {
            foreach ($responses as $response) {
                $checkQuery = "SELECT * FROM user_responses WHERE user_id = :userId AND survey_question_id = :surveyQuestionId";
                $checkStmt = Database::getConn()->prepare($checkQuery);
                $checkStmt->bindParam(':userId', $userId);
                $checkStmt->bindParam(':surveyQuestionId', $response->surveyQuestionId);
                $checkStmt->execute();
                if ($checkStmt->rowCount() > 0) {
                    $updateQuery = "UPDATE user_responses SET response = :response WHERE user_id = :userId AND survey_question_id = :surveyQuestionId";
                    $updateStmt = Database::getConn()->prepare($updateQuery);
                    $updateStmt->bindParam(':userId', $userId);
                    $updateStmt->bindParam(':surveyQuestionId', $response->surveyQuestionId);
                    $updateStmt->bindParam(':response', $response->response);
                    $updateStmt->execute();
                } else {
                    $insertQuery = "INSERT INTO user_responses (user_id, survey_question_id, response) VALUES (:userId, :surveyQuestionId, :response)";
                    $insertStmt = Database::getConn()->prepare($insertQuery);
                    $insertStmt->bindParam(':userId', $userId);
                    $insertStmt->bindParam(':surveyQuestionId', $response->surveyQuestionId);
                    $insertStmt->bindParam(':response', $response->response);
                    $insertStmt->execute();
                }
            }
            Database::getConn()->commit();
        } catch (Exception $e) {
            Database::getConn()->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Error al registrar las respuestas de la encuesta.']);
            exit;
        }
    }

    public static function createSurveyQuestion($question)
    {
        $query = "INSERT INTO survey_questions (question_text) VALUES (:question_text)";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':question_text', $question);
        $stmt->execute();
    }
    
    public static function deleteAllSurveyQuestions()
    {
        $query = "DELETE FROM survey_questions";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute();
    }
}
