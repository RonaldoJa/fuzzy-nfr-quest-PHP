<?php
require_once 'config/Database.php';
require_once 'entities/GameRoomEntity.php';
require_once 'entities/GameScoreEntity.php';
require_once 'entities/QuestionEntity.php';

class GameService
{

    public static function deleteRoomStatus($conn, $game_room_id, $status)
    {
        $query = "SELECT id FROM game_rooms WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $game_room_id);
        $stmt->execute();

        $gameRoomData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$gameRoomData) {
            throw new Exception("No se pudo obtener la información de la sala recién creada.");
        }


        $query = "UPDATE game_rooms SET status = :status WHERE id = :game_room_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':game_room_id', $game_room_id);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
    }



    public static function createGameRoom($conn, GameRoomEntity $gameRoom)
    {
        try {
            $query = "INSERT INTO game_rooms (code, user_id_created, expiration_date, created_at) VALUES (:code, :user_id_created, :expiration_date, now())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':code', $gameRoom->code);
            $stmt->bindParam(':user_id_created', $gameRoom->user_id_created);
            $stmt->bindParam(':expiration_date', $gameRoom->expiration_date);

            if (!$stmt->execute()) {
                throw new Exception("Error al crear la sala de juego en la base de datos.");
            }

            $gameRoomId = Database::getConn()->lastInsertId();

            $query = "SELECT * FROM game_rooms WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $gameRoomId);
            $stmt->execute();

            $gameRoomData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$gameRoomData) {
                throw new Exception("No se pudo obtener la información de la sala recién creada.");
            }

            return $gameRoomData;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public static function createQuestions(QuestionEntity $question, $gameRoomId)
    {
        $query = "INSERT INTO questions 
        (game_room_id, nfr, variable, feedback1, value, feedback2, recomend, other_recommended_values, feedback3, validar, created_at)
        VALUES (:game_room_id, :nfr, :variable, :feedback1, :value, :feedback2, :recomend, :other_recommended_values, :feedback3, :validar, now())";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':game_room_id', $gameRoomId);
        $stmt->bindParam(':nfr', $question->nfr);
        $stmt->bindParam(':variable', $question->variable);
        $stmt->bindParam(':feedback1', $question->feedback1);
        $stmt->bindParam(':value', $question->value);
        $stmt->bindParam(':feedback2', $question->feedback2);
        $stmt->bindParam(':recomend', $question->recomend);
        $stmt->bindParam(':other_recommended_values', $question->other_recommended_values);
        $stmt->bindParam(':feedback3', $question->feedback3);
        $stmt->bindParam(':validar', $question->validar);
        $stmt->execute();
        return Database::getConn()->lastInsertId();
    }

    public static function getGameRoomByCode($code)
    {
        $query = "SELECT * FROM game_rooms WHERE code = :code limit 1";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();

        $gameRoom = $stmt->fetch(PDO::FETCH_ASSOC);

        return $gameRoom;
    }

    public static function getGameRoomById($room_id)
    {
        $query = "SELECT * FROM game_rooms WHERE id = :id limit 1";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':id', $room_id);
        $stmt->execute();

        $gameRoom = $stmt->fetch(PDO::FETCH_ASSOC);

        return $gameRoom;
    }

    public static function getGameScoreByUser($game_room_id, $user_id)
    {
        $query = "SELECT * FROM game_score WHERE game_room_id = :game_room_id and user_id = :user_id limit 1";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':game_room_id', $game_room_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $gameScore = $stmt->fetch(PDO::FETCH_ASSOC);

        return $gameScore;
    }

    public static function getGameScoreByGameRoomId($conn, $game_room_id)
    {
        $query = "SELECT 
                        gs.*, 
                        u.id AS user_id, 
                        u.name AS name, 
                        u.last_name AS last_name
                    FROM 
                        game_score gs
                    JOIN 
                        users u ON gs.user_id = u.id
                    WHERE 
                        gs.game_room_id = :game_room_id
                ";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':game_room_id', $game_room_id);
        $stmt->execute();
        $gameScore = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $gameScore;
    }

    public static function createGameScore($conn, GameScoreEntity $gameScore)
    {
        $answered_questions = json_encode($gameScore->answered_questions);

        $query = "INSERT INTO game_score (user_id, score, duration, game_room_id, answered_questions, created_at) VALUES (:user_id, :score, :duration, :game_room_id, :answered_questions, now())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $gameScore->user_id);
        $stmt->bindParam(':score', $gameScore->score);
        $stmt->bindParam(':duration', $gameScore->duration);
        $stmt->bindParam(':game_room_id', $gameScore->game_room_id);
        $stmt->bindParam(':answered_questions', $answered_questions);
        $stmt->execute();
        return Database::getConn()->lastInsertId();
    }

    public static function editGameRoom($expiration_date, $game_room_id)
    {
        $query = "UPDATE game_rooms SET expiration_date = :expiration_date, updated_at = now() WHERE id = :id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':id', $game_room_id);
        $stmt->bindParam(':expiration_date', $expiration_date);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function editQuestionRoom(QuestionEntity $question, $questionId)
    {
        $query = "UPDATE questions SET nfr = :nfr, variable = :variable, feedback1 = :feedback1, value = :value, feedback2 = :feedback2, recomend = :recomend, other_recommended_values = :other_recommended_values, feedback3 = :feedback3, validar = :validar WHERE id = :id";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':id', $questionId);
        $stmt->bindParam(':nfr', $question->nfr);
        $stmt->bindParam(':variable', $question->variable);
        $stmt->bindParam(':feedback1', $question->feedback1);
        $stmt->bindParam(':value', $question->value);
        $stmt->bindParam(':feedback2', $question->feedback2);
        $stmt->bindParam(':recomend', $question->recomend);
        $stmt->bindParam(':other_recommended_values', $question->other_recommended_values);
        $stmt->bindParam(':feedback3', $question->feedback3);
        $stmt->bindParam(':validar', $question->validar);
        $stmt->execute();
    }

}
