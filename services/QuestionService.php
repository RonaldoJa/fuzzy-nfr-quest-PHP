<?php
require_once 'config/Database.php';
require_once 'entities/GameRoomEntity.php';
require_once 'entities/QuestionEntity.php';

class QuestionService
{
    public static function getQuestionsbyGameRoomId($gameRoomId)
    {
        $query = "SELECT id, nfr, other_recommended_values FROM questions WHERE game_room_id = :game_room_id AND game_room_id is not null";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':game_room_id', $gameRoomId);
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $questions;
    }

    public static function find($conn, $question_id)
    {
        $query = "SELECT * FROM questions WHERE id = :question_id limit 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':question_id', $question_id);
        $stmt->execute();
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
        return $question;
    }
}
