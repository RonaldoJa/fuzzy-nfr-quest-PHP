<?php

require_once 'services/QuestionService.php';
require_once 'services/GameService.php';
require_once 'helpers/globalHelper.php';
require_once 'config/Database.php';

class QuizController
{

    public static function store($user_id)
    {
        try {
            $conn = Database::getConn();

            $data = json_decode(file_get_contents('php://input'), true);

            $game_room_id = trim($data['game_room_id']) ?? null;
            $duration = trim($data['duration']) ?? null;
            $answers = $data['answers'] ?? [];

            if (empty($game_room_id) || empty($duration) || empty($answers)) {
                return GlobalHelper::generalResponse(null, 'Los campos game_room_id, duration y answers son obligatorios. ', 400);
            }

            $totalScore = 0;
            $maxScore = 0;
            $result = [];

            $conn->beginTransaction();

            foreach ($answers as $response) {

                $question = QuestionService::find($conn, $response['id']);

                if (!$question) {
                    return GlobalHelper::generalResponse(null, 'Pregunta no encontrada', 404);
                }

                $replace_validar = str_replace(',', '.', $question['validar']);
                $weights = explode('/', $replace_validar);

                $weightVariable = isset($weights[0]) ? floatval($weights[0]) : 0;
                $weightValue = isset($weights[1]) ? floatval($weights[1]) : 0;
                $weightRecomend = isset($weights[2]) ? floatval($weights[2]) : 0;

                $correctVariable = $question['variable'] === trim($response['variable']);
                $correctValue = $question['value'] === trim($response['value']);
                $correctRecomend = $question['recomend'] === trim($response['recomend']);

                $scoreVariable = $correctVariable ? $weightVariable : 0;
                $scoreValue = $correctValue ? $weightValue : 0;
                $scoreRecomend = $correctRecomend ? $weightRecomend : 0;

                $score =  $scoreVariable +   $scoreValue + $scoreRecomend;

                $totalScore += $score;

                $maxScore +=  $weightVariable + $weightValue + $weightRecomend;

                $feedbackVariable = null;
                $feedbackValue = null;
                $feedbackRecomend = null;

                if (!$correctVariable) {
                    $feedbackVariable = $question['feedback1'];
                }
                if (!$correctValue) {
                    $feedbackValue = $question['feedback2'];
                }
                if (!$correctRecomend) {
                    $feedbackRecomend = $question['feedback3'];
                }

                $result[] = [
                    'id' => $response['id'],
                    'nfr' => $question['nfr'],
                    'user_variable' => $response['variable'],
                    'feedback_variable' => $feedbackVariable,
                    'correct_variable' => $correctVariable,
                    'user_value' => $response['value'],
                    'feedback_value' => $feedbackValue,
                    'correct_value' => $correctValue,
                    'user_recomend' => $response['recomend'],
                    'feedback_recomend' => $feedbackRecomend,
                    'correct_recomend' => $correctRecomend,
                ];
            }

            $finalScore = round($maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0, 2);

            $gameScore = new GameScoreEntity($game_room_id, $user_id, $finalScore, $duration, $result);

            GameService::createGameScore($conn, $gameScore);

            $conn->commit();
            return GlobalHelper::generalResponse(['total_score' => $finalScore, 'result' => $result], 'Cuestionario completado con éxito.');
        } catch (\Throwable $th) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }
}
