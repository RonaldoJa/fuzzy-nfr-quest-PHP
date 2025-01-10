<?php
require_once 'config/Database.php';


class ReportService
{
    public static function generalSummary($conn, $game_room_id)
    {
        $query = "SELECT 
                    gs.game_room_id,
                    gr.code as game_room_code,
                    (SELECT AVG(inner_gs.score) 
                    FROM game_score inner_gs 
                    WHERE inner_gs.game_room_id = gs.game_room_id) as average_score,
                    MAX(gs.score) as highest_score,
                    CONCAT(u.last_name, ' ', u.name) as highest_scorer_name,
                    u.id as highest_scorer_id,
                    gs.score,
                    gs.duration,
                    (SELECT COUNT(q.id) 
                    FROM questions q 
                    WHERE q.game_room_id = gs.game_room_id) as total_questions
                    FROM game_score gs
                    JOIN users u ON gs.user_id = u.id
                    JOIN game_rooms gr ON gs.game_room_id = gr.id
                    WHERE gs.game_room_id = :game_room_id
                    AND gs.score = (
                        SELECT MAX(inner_gs.score)
                        FROM game_score inner_gs
                        WHERE inner_gs.game_room_id = gs.game_room_id
                    )
                    ORDER BY 
                        gs.duration ASC, 
                        gs.created_at ASC
                    LIMIT 1;
                ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':game_room_id', $game_room_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
