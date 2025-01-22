<?php

require_once 'services/GameService.php';
require_once 'services/UserService.php';
require_once 'entities/GameRoomEntity.php';
require_once 'config/Database.php';

class GameController
{
    public static function getGameHistory($user_id)
    {
        try {

            $query = "
            SELECT 
                gs.*, 
                gr.id AS game_room_id, 
                gr.code AS game_room_code, 
                gr.user_id_created AS game_room_user_id_created, 
                gr.created_at AS game_room_created_at, 
                gr.expiration_date AS game_room_expiration_date, 
                gr.status AS game_room_status
            FROM 
                game_score gs
            INNER JOIN 
                game_rooms gr 
            ON 
                gs.game_room_id = gr.id
            WHERE 
                gs.user_id = :user_id
            ORDER BY 
                gs.created_at DESC";

            $stmt = Database::getConn()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $gameHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $formattedHistory = [];
            foreach ($gameHistory as $row) {
                $formattedHistory[] = [
                    "id" => $row['id'],
                    "user_id" => $row['user_id'],
                    "game_room_id" => $row['game_room_id'],
                    "score" => $row['score'],
                    "answered_questions" => json_decode($row['answered_questions'], true),
                    "duration" => $row['duration'],
                    "created_at" => $row['created_at'],
                    "game_room" => [
                        "id" => $row['game_room_id'],
                        "code" => $row['game_room_code'],
                        "user_id_created" => $row['game_room_user_id_created'],
                        "created_at" => $row['game_room_created_at'],
                        "expiration_date" => $row['game_room_expiration_date'],
                        "status" => (bool)$row['game_room_status'],
                    ],
                ];
            }

            return GlobalHelper::generalResponse($formattedHistory, 'Historial de juegos recuperado con éxito.');
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }


    public static function getGameRooms($user_id)
    {
        try {
            $query = "select * from game_rooms where user_id_created = :user_id";
            $stmt = Database::getConn()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $gameHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return GlobalHelper::generalResponse($gameHistory, 'Proceso exitoso.');
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }

    public static function deleteRoomForStatus($user_id)
    {
        try {

            // if (empty($gameRoomId) || empty($status)) {
            //     return GlobalHelper::generalResponse(null, 'Los campos game_room_id y status son obligatorios. ', 400);
            // }

            $conn = Database::getConn();
            $isTeacher = UserService::isTeacher($user_id);

            if (!$isTeacher) {
                return GlobalHelper::generalResponse(null, 'Acceso denegado. Solo los docentes pueden crear salas de juegos.', 403);
            }

            $conn->beginTransaction();

            $data = json_decode(file_get_contents('php://input'), true);

            $gameRoomId = $data['game_room_id'] ?? null;
            $status = $data['status'] ?? null;
            
            $gameRoom = GameService::deleteRoomStatus($conn, $gameRoomId, $status);


            $conn->commit();

            return GlobalHelper::generalResponse(null, 'Proceso exitoso.');
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }

    public static function createRoomGameQuestions($user_id)
    {
        try {
            $conn = Database::getConn();
            $isTeacher = UserService::isTeacher($user_id);

            if (!$isTeacher) {
                return GlobalHelper::generalResponse(null, 'Acceso denegado. Solo los docentes pueden crear salas de juegos.', 403);
            }

            $conn->beginTransaction();

            $data = json_decode(file_get_contents('php://input'), true);

            $expiration_date = trim($data['expiration_date']) ?? null;
            $questions = $data['questions'] ?? null;


            if (empty($expiration_date) || empty($questions)) {
                return GlobalHelper::generalResponse(null, 'Los campos expiration_date y questions son obligatorios. ', 400);
            }

            if (!GlobalHelper::isValidDate($expiration_date)) {
                return GlobalHelper::generalResponse(null, 'La fecha de expiración no es válida', 400);
            }

            if (!GlobalHelper::validateArrayFields($questions)) {
                return GlobalHelper::generalResponse(null, 'Por favor revisa los campos dentro del array questions que esten todo completos.', 400);
            }

            $code = GlobalHelper::generateRandomString(6);
            //$expiration_date = date('Y-m-d H:i:s', strtotime('+1 day'));

            $gameRoomEntity = new GameRoomEntity($code, $user_id, $expiration_date);

            $gameRoom = GameService::createGameRoom($conn, $gameRoomEntity);

            foreach ($questions as $questionData) {
                $question = new QuestionEntity(trim($questionData['nfr']), trim($questionData['variable']), trim($questionData['feedback1']), trim($questionData['value']), trim($questionData['feedback2']), trim($questionData['recomend']), trim($questionData['other_recommended_values']), trim($questionData['feedback3']), trim($questionData['validar']));
                GameService::createQuestions($question, $gameRoom['id']);
            }

            $conn->commit();

            return GlobalHelper::generalResponse(null, 'La sala de juegos se ha creado correctamente, ' .
                'Por favor comparte este código <strong>' . $gameRoom['code'] . '</strong> con tus estudiantes, ' .
                'Recuerda que esta sala expira el <strong>' . $gameRoom['expiration_date'] . '</strong>.');
        } catch (\Throwable $th) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }

    // public static function editRoomGameQuestions($user_id)
    // {
    //     try {
    //         $conn = Database::getConn();
    //         $isTeacher = UserService::isTeacher($user_id);

    //         if (!$isTeacher) {
    //             return GlobalHelper::generalResponse(null, 'Acceso denegado. Solo los docentes pueden crear salas de juegos.', 403);
    //         }

    //         $conn->beginTransaction();

    //         $data = json_decode(file_get_contents('php://input'), true);

    //         $gameRoomId = $data['game_room_id'] ?? null;
    //         $questions = $data['questions'] ?? null;

    //         if (empty($gameRoomId) || empty($questions)) {
    //             return GlobalHelper::generalResponse(null, 'Los campos game_room_id y questions son obligatorios. ', 400);
    //         }

    //         if (!GlobalHelper::validateArrayFields($questions)) {
    //             return GlobalHelper::generalResponse(null, 'Por favor revisa los campos dentro del array questions que esten todo completos.', 400);
    //         }

    //         foreach ($questions as $questionData) {
    //             $question = new QuestionEntity(trim($questionData['nfr']), trim($questionData['variable']), trim($questionData['feedback1']), trim($questionData['value']), trim($questionData['feedback2']), trim($questionData['recomend']), trim($questionData['other_recommended_values']), trim($questionData['feedback3']), trim($questionData['validar']));
    //             GameService::editGameRoom($question, $gameRoomId);
    //         }

    //         $conn->commit();

    //         return GlobalHelper::generalResponse(null, 'Las preguntas de la sala de juegos se han actualizado correctamente.');
    //     } catch (\Throwable $th) {
    //         if (isset($conn)) {
    //             $conn->rollBack();
    //         }
    //         return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
    //     }
    // }

    public static function editQuestionRooms($user_id)
    {
        try {
            $conn = Database::getConn();
            $isTeacher = UserService::isTeacher($user_id);

            if (!$isTeacher) {
                return GlobalHelper::generalResponse(null, 'Acceso denegado. Solo los docentes pueden crear salas de juegos.', 403);
            }

            $conn->beginTransaction();

            $data = json_decode(file_get_contents('php://input'), true);
            $questions = $data['questions'] ?? null;
            $questionId = $data['questionId'] ?? null;

            if (empty($questions) || !is_array($questions)) {
                return GlobalHelper::generalResponse(null, 'Se requiere un array de preguntas.', 400);
            }

            if (empty($questionId)) {
                return GlobalHelper::generalResponse(null, 'Se requiere un questionId.', 400);
            }

            foreach ($questions as $questionData) {
                if (empty($questionData)) {
                    return GlobalHelper::generalResponse(null, 'Cada pregunta debe tener sus campos completos', 400);
                }

                $questionEntity = new QuestionEntity(
                    trim($questionData['nfr']),
                    trim($questionData['variable']),
                    trim($questionData['feedback1']),
                    trim($questionData['value']),
                    trim($questionData['feedback2']),
                    trim($questionData['recomend']),
                    trim($questionData['other_recommended_values']),
                    trim($questionData['feedback3']),
                    trim($questionData['validar'])
                );
                GameService::editQuestionRoom($questionEntity, $questionId);
            }

            $conn->commit();
            return GlobalHelper::generalResponse(null, 'Las preguntas se han actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }
}
