<?php

require_once 'config/Database.php';

class QuestionService{
    

    public static function insertQuestions(array $questions, int $user_id): int
    {

        try {
            // Iniciar una transacción
            Database::getConn()->beginTransaction();

            // Generar un código único de 6 caracteres
            $code = self::generateUniqueCode(6);

            // Calcular la fecha de expiración (7 días a partir de ahora)
            $expiration_date = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Insertar en game_rooms
            $queryGameRoom = "INSERT INTO game_rooms (code, user_id_created, expiration_date) VALUES (:code, :user_id_created, :expiration_date)";
            $stmtGameRoom = Database::getConn()->prepare($queryGameRoom);
            
            // Sanitizar los datos antes de la inserción
            $codeSanitized = htmlspecialchars($code);
            $expirationDateSanitized = htmlspecialchars($expiration_date);

            $stmtGameRoom->bindParam(':code', $codeSanitized);
            $stmtGameRoom->bindParam(':user_id_created', $user_id, PDO::PARAM_INT);
            $stmtGameRoom->bindParam(':expiration_date', $expirationDateSanitized);

            if (!$stmtGameRoom->execute()) {
                throw new Exception('Ocurrió un error al guardar el Game Room.');
            }

            // Obtener el ID del game_room recién creado
            $game_room_id = (int)Database::getConn()->lastInsertId();

            // Preparar la sentencia SQL para insertar en la tabla questions
            $queryQuestion = "
                INSERT INTO questions 
                    (nfr, variable, feedback1, value, feedback2, other_recommended_values, recomend, feedback3, validar, game_room_id)
                VALUES 
                    (:nfr, :variable, :feedback1, :value, :feedback2, :other_recommended_values, :recomend, :feedback3, :validar, :game_room_id)
            ";
            $stmtQuestion = Database::getConn()->prepare($queryQuestion);

            foreach ($questions as $question) {
                // Validar y mapear los campos del objeto JSON a los parámetros de la base de datos
                $nfr = htmlspecialchars($question['RNF'] ?? '');
                $variable = htmlspecialchars(trim($question['linguistic_variable'] ?? ''));
                $feedback1 = htmlspecialchars($question['feedback_linguistic_variable'] ?? '');
                $value = htmlspecialchars(trim($question['linguistic_value'] ?? ''));
                $feedback2 = htmlspecialchars($question['feedback_linguistic_value'] ?? '');
                $recomend = htmlspecialchars(trim($question['recommended_linguistic_value'] ?? ''));
                $feedback3 = htmlspecialchars($question['feedback_recommended_linguistic_value'] ?? '');
                $other_recommended_values = htmlspecialchars(trim($question['other_linguistic_values'] ?? ''));
                $validar = htmlspecialchars(trim($question['weights'] ?? ''));

                $stmtQuestion->bindParam(':nfr', $nfr);
                $stmtQuestion->bindParam(':variable', $variable);
                $stmtQuestion->bindParam(':feedback1', $feedback1);
                $stmtQuestion->bindParam(':value', $value);
                $stmtQuestion->bindParam(':feedback2', $feedback2);
                $stmtQuestion->bindParam(':other_recommended_values', $other_recommended_values);
                $stmtQuestion->bindParam(':recomend', $recomend);
                $stmtQuestion->bindParam(':feedback3', $feedback3);
                $stmtQuestion->bindParam(':validar', $validar);
                $stmtQuestion->bindParam(':game_room_id', $game_room_id, PDO::PARAM_INT);

                if (!$stmtQuestion->execute()) {
                    throw new Exception('Ocurrió un error al guardar una de las preguntas.');
                }
            }

            Database::getConn()->commit();
            return $game_room_id;
        } catch (Exception $e) {
            Database::getConn()->rollBack();
            throw new Exception('Error al insertar datos: ' . $e->getMessage());
        }
    }

    private static function generateUniqueCode(int $length = 6): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $maxAttempts = 1000;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, $charactersLength - 1)];
            }

            $queryGameRoom = "SELECT COUNT(code) FROM game_rooms WHERE code = :code";
            $stmtGameRoom = Database::getConn()->prepare($queryGameRoom);
            $stmtGameRoom->bindParam(':code', $code);
            if (!$stmtGameRoom->execute()) {
                throw new Exception('Ocurrió un error al consultar el codigo.');
            }

            $count = (int)$stmtGameRoom->fetchColumn();

            if ($count === 0) {
                return $code;
            }
        }

        throw new Exception('No se pudo generar un código único después de múltiples intentos.');
    }

}