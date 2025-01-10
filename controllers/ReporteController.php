<?php

require_once 'bookstores/dompdf/autoload.inc.php';
require_once 'helpers/globalHelper.php';
require_once 'config/Database.php';
require_once 'services/GameService.php';
require_once 'services/ReportService.php';

class ReporteController
{

    public static function reportTeacher($name, $last_name)
    {
        try {
            $conn = Database::getConn();
            $dompdf = new Dompdf\Dompdf();

            $data = json_decode(file_get_contents('php://input'), true);

            $game_room_id = trim($data['game_room_id']) ?? null;

            if (empty($game_room_id)) {
                return GlobalHelper::generalResponse(null, 'El campo game_room_id es obligatorio.', 400);
            }

            $generated_at = date('Y-m-d H:i:s');
            $year = date('Y');
            $fullNameTeacher = $name . ' ' . $last_name;

            $conn->beginTransaction();

            $scores = GameService::getGameScoreByGameRoomId($conn, $game_room_id);

            if (count($scores) == 0) {
                return GlobalHelper::generalResponse(null, 'El informe no está disponible porque todavía no hay datos de juego de los estudiantes.', 404);
            }

            $generalSummary = ReportService::generalSummary($conn, $game_room_id);

            $reportDetails = array_map(function ($score) {

                $answeredQuestions = json_decode($score['answered_questions'], true);

                $correct = array_reduce($answeredQuestions, function ($carry, $question) {
                    return $carry +
                        ($question['correct_variable'] === true ? 1 : 0) +
                        ($question['correct_value'] === true ? 1 : 0) +
                        ($question['correct_recomend'] === true ? 1 : 0);
                }, 0);

                $incorrect = array_reduce($answeredQuestions, function ($carry, $question) {
                    return $carry +
                        ($question['correct_variable'] === false ? 1 : 0) +
                        ($question['correct_value'] === false ? 1 : 0) +
                        ($question['correct_recomend'] === false ? 1 : 0);
                }, 0);

                $totalQuestions = $correct + $incorrect;

                return [
                    'user_id' => $score['user_id'],
                    'last_name' => $score['last_name'],
                    'name' => $score['name'],
                    'total_questions' => $totalQuestions,
                    'correct' => $correct,
                    'incorrect' => $incorrect,
                    'score' => $score['score'],
                    'duration' => $score['duration'],
                    'created_at' => $score['created_at'],
                ];
            }, $scores);

            $html = file_get_contents('resources/templates/game_room_report.html');

            $reportDetailsHtml = "";
            foreach ($reportDetails as $reportDetail) {
                $reportDetailsHtml .= "<tr>";
                $reportDetailsHtml .= "<td>" . $reportDetail['user_id'] . "</td>";
                $reportDetailsHtml .= "<td>" . $reportDetail['last_name'] . "</td>";
                $reportDetailsHtml .= "<td>" . $reportDetail['name'] . "</td>";
                $reportDetailsHtml .= "<td>" . $reportDetail['correct'] . "</td>";
                $reportDetailsHtml .= "<td>" . $reportDetail['incorrect'] . "</td>";
                $reportDetailsHtml .= "<td>" . $reportDetail['total_questions'] . "</td>";
                $reportDetailsHtml .= "<td>" . round($reportDetail['score'], 2) . "</td>";
                $reportDetailsHtml .= "<td>" . $reportDetail['duration'] . "</td>";
                $reportDetailsHtml .= "<td>" . $reportDetail['created_at'] . "</td>";
                $reportDetailsHtml .= "</tr>";
            }

            $imagen = 'resources/img/logo.png';
            $imagenBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($imagen));


            $html = str_replace('{{generated_at}}', $generated_at, $html);
            $html = str_replace('{{fullNameTeacher}}', $fullNameTeacher, $html);
            $html = str_replace('{{code}}', $generalSummary["game_room_code"], $html);

            $html = str_replace('{{average_score}}', round($generalSummary["average_score"], 2), $html);
            $html = str_replace('{{highest_score}}', round($generalSummary["highest_score"], 2), $html);
            $html = str_replace('{{highest_scorer_name}}', $generalSummary["highest_scorer_name"], $html);
            $html = str_replace('{{highest_scorer_id}}', $generalSummary["highest_scorer_id"], $html);
            $html = str_replace('{{total_questions}}', $generalSummary["total_questions"], $html);

            $html = str_replace('{{reportDetails}}', $reportDetailsHtml, $html);

            $html = str_replace('{{year}}', $year, $html);
            $html = str_replace('{{imagen}}', $imagenBase64, $html);

            $fileName = 'REPORTE-SALA-' . $generalSummary["game_room_code"] . '-' . date('Y-m-d_H-i-s') . '.pdf';

            $conn->commit();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream($fileName);
        } catch (\Throwable $th) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }
}
