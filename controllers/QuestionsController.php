<?php

require_once 'helpers/globalHelper.php';
require_once 'services/GameService.php';
require_once 'services/QuestionService.php';

class QuestionsController
{
    public static function questionsByCode($user_id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $code = trim($data['code']) ?? null;
            $userId = $user_id;

            if (empty($code)) {
                return GlobalHelper::generalResponse(null, 'El campo code es obligatorio.', 400);
            }

            $gameRoomExists = GameService::getGameRoomByCode($code);

            if (!$gameRoomExists) {
                return GlobalHelper::generalResponse(null, 'La sala de juego que estás buscando no existe. Verifica el código e inténtalo nuevamente.', 404);
            }

            $gameScore = GameService::getGameScoreByUser($gameRoomExists['id'], $userId);

            if ($gameScore) {
                return GlobalHelper::generalResponse(null, 'Ya completaste el juego en esta sala. Por favor, únete a otra sala.', 403);
            }

            if (!$gameRoomExists['status']) {
                return GlobalHelper::generalResponse(null, 'Esta sala de juego ya no está disponible. Por favor, verifique con su docente.', 403);
            }

            if ($gameRoomExists['expiration_date'] < date('Y-m-d H:i:s')) {
                return GlobalHelper::generalResponse(null, 'Esta sala de juego ya no está disponible porque ha expirado. Por favor, verifique con el docente o inicie una nueva sala', 403);
            }

            $questions = QuestionService::getQuestionsbyGameRoomId($gameRoomExists['id']);

            $data = [
                'game_room_id' => $gameRoomExists['id'],
                'questions' => $questions,
                'message' => 'Requerimientos no funcionales encontrados.',
            ];

            http_response_code(200);
            echo json_encode($data);
            return;
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }
}
