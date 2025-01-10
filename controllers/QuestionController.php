
<?php

require_once 'services/QuestionService.php';

class QuestionController
{

    public static function InsertQuestions($user_id)
    {
        $json = file_get_contents('php://input');
        
        // Decodificar el JSON a un arreglo asociativo
        $questions = json_decode($json, true);
        
        try {
            // Insertar game_room y preguntas usando el servicio
            $game_room_id = QuestionService::insertQuestions($questions, (int)$user_id);

            // Responder con éxito
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Preguntas y Game Room insertados correctamente.',
                'game_room_id' => $game_room_id
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar la solicitud.',
                'error' => $e->getMessage()
            ]);
        }
        
    }

    
}