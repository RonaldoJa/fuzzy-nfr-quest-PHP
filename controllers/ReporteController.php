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
            $language = isset($data['language']) ? trim($data['language']) : 'es';

            if (empty($game_room_id)) {
                return GlobalHelper::generalResponse(null, 'El campo game_room_id es obligatorio.', 400);
            }

            $generated_at = date('Y-m-d H:i:s');
            $year = date('Y');
            $fullNameTeacher = $name . ' ' . $last_name;

            $conn->beginTransaction();

            $scores = GameService::getGameScoreByGameRoomId($conn, $game_room_id);
            $reportsNFR = [];

            foreach ($scores as $group) {
                $answered_questions = json_decode($group["answered_questions"], true);

                foreach ($answered_questions as $record) {
                    $id = $record['id'];

                    $found = false;
                    foreach ($reportsNFR as &$result) {
                        if ($result['id'] === $id) {
                            if ($record['correct_value']) {
                                $result['correct_value_true']++;
                            } else {
                                $result['correct_value_false']++;
                            }

                            if ($record['correct_recomend']) {
                                $result['correct_recomend_true']++;
                            } else {
                                $result['correct_recomend_false']++;
                            }

                            if ($record['correct_variable']) {
                                $result['correct_variable_true']++;
                            } else {
                                $result['correct_variable_false']++;
                            }

                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $reportsNFR[] = [
                            'id' => $id,
                            "nfr" => $record['nfr'],
                            'correct_value_true' => $record['correct_value'] ? 1 : 0,
                            'correct_value_false' => $record['correct_value'] ? 0 : 1,
                            'correct_recomend_true' => $record['correct_recomend'] ? 1 : 0,
                            'correct_recomend_false' => $record['correct_recomend'] ? 0 : 1,
                            'correct_variable_true' => $record['correct_variable'] ? 1 : 0,
                            'correct_variable_false' => $record['correct_variable'] ? 0 : 1
                        ];
                    }
                }
            }

            if (count($scores) == 0) {
                if ($language == 'es') {
                    return GlobalHelper::generalResponse(null, 'El informe no está disponible porque todavía no hay datos de juego de los estudiantes.', 404);
                } else {
                    return GlobalHelper::generalResponse(null, 'The report is not available because there is no data on student play yet.', 404);
                }
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

            $reportNFRHtml = "";
            foreach ($reportsNFR as $reportNFR) {
                $reportNFRHtml .= "<tr>";
                $reportNFRHtml .= "<td>" . $reportNFR['id'] . "</td>";
                $reportNFRHtml .= "<td>" . $reportNFR['nfr'] . "</td>";
                $reportNFRHtml .= "<td>" . $reportNFR['correct_variable_true'] . "</td>";
                $reportNFRHtml .= "<td>" . $reportNFR['correct_variable_false'] . "</td>";
                $reportNFRHtml .= "<td>" . $reportNFR['correct_value_true'] . "</td>";
                $reportNFRHtml .= "<td>" . $reportNFR['correct_value_false'] . "</td>";
                $reportNFRHtml .= "<td>" . $reportNFR['correct_recomend_true'] . "</td>";
                $reportNFRHtml .= "<td>" . $reportNFR['correct_recomend_false'] . "</td>";
                $reportNFRHtml .= "</tr>";
            }

            $imagen = 'resources/img/logo.png';
            $imagenBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($imagen));

            if ($language == 'es') {
                $html = str_replace('{{title}}', 'Reporte Académico', $html);
                $html = str_replace('{{generation_date}}', 'Fecha de generación: ', $html);
                $html = str_replace('{{teacher_title}}', 'Docente: ', $html);
                $html = str_replace('{{code_title}}', 'Código de Sala: ', $html);
                $html = str_replace('{{subtitle_1}}', 'Resumen General', $html);
                $html = str_replace('{{table_general_item1}}', 'Total de RNF', $html);
                $html = str_replace('{{table_general_item2}}', 'Promedio', $html);
                $html = str_replace('{{table_general_item3}}', 'Puntaje Más Alto', $html);
                $html = str_replace('{{table_general_item4}}', 'Estudiante con el Puntaje Más Alto', $html);
                $html = str_replace('{{subtitle_2}}', 'Detalle por Estudiante', $html);
                $html = str_replace('{{table_student_item1}}', 'Apellidos', $html);
                $html = str_replace('{{table_student_item2}}', 'Nombres', $html);
                $html = str_replace('{{table_student_item3}}', 'Aciertos', $html);
                $html = str_replace('{{table_student_item4}}', 'Desaciertos', $html);
                $html = str_replace('{{table_student_item5}}', 'Puntaje', $html);
                $html = str_replace('{{table_student_item6}}', 'Duración', $html);
                $html = str_replace('{{table_student_item7}}', 'Fecha Registro', $html);
                $html = str_replace('{{subtitle_3}}', 'Detalle por RNF', $html);
                $html = str_replace('{{table_NFR_item1}}', 'RNF', $html);
                $html = str_replace('{{table_NFR_item2}}', 'Variable Lingüística', $html);
                $html = str_replace('{{table_NFR_item3}}', 'Valor Lingüístico', $html);
                $html = str_replace('{{table_NFR_item4}}', 'Recomendación', $html);
                $html = str_replace('{{table_NFR_item5}}', 'Correctas', $html);
                $html = str_replace('{{table_NFR_item6}}', 'Incorrectas', $html);
                $html = str_replace('{{footer}}', 'Reporte generado por el sistema ', $html);
            } else {
                $html = str_replace('{{title}}', 'Academic Report', $html);
                $html = str_replace('{{generation_date}}', 'Generation date: ', $html);
                $html = str_replace('{{teacher_title}}', 'Teacher: ', $html);
                $html = str_replace('{{code_title}}', 'Room Code: ', $html);
                $html = str_replace('{{subtitle_1}}', 'General Summary', $html);
                $html = str_replace('{{table_general_item1}}', 'Total RNF', $html);
                $html = str_replace('{{table_general_item2}}', 'Average', $html);
                $html = str_replace('{{table_general_item3}}', 'Highest Score', $html);
                $html = str_replace('{{table_general_item4}}', 'Student with the Highest Score', $html);
                $html = str_replace('{{subtitle_2}}', 'Detail by Student', $html);
                $html = str_replace('{{table_student_item1}}', 'Surnames', $html);
                $html = str_replace('{{table_student_item2}}', 'Names', $html);
                $html = str_replace('{{table_student_item3}}', 'Successes', $html);
                $html = str_replace('{{table_student_item4}}', 'Mistakes', $html);
                $html = str_replace('{{table_student_item5}}', 'Score', $html);
                $html = str_replace('{{table_student_item6}}', 'Duration', $html);
                $html = str_replace('{{table_student_item7}}', 'Registration Date', $html);
                $html = str_replace('{{subtitle_3}}', 'Detail by NFR', $html);
                $html = str_replace('{{table_NFR_item1}}', 'NFR', $html);
                $html = str_replace('{{table_NFR_item2}}', 'Linguistic Variable', $html);
                $html = str_replace('{{table_NFR_item3}}', 'Linguistic Value', $html);
                $html = str_replace('{{table_NFR_item4}}', 'Recommendation', $html);
                $html = str_replace('{{table_NFR_item5}}', 'Correct', $html);
                $html = str_replace('{{table_NFR_item6}}', 'Incorrect', $html);
                $html = str_replace('{{footer}}', 'Report generated by the system ', $html);
            }

            $html = str_replace('{{generated_at}}', $generated_at, $html);
            $html = str_replace('{{fullNameTeacher}}', $fullNameTeacher, $html);
            $html = str_replace('{{code}}', $generalSummary["game_room_code"], $html);

            $html = str_replace('{{average_score}}', round($generalSummary["average_score"], 2), $html);
            $html = str_replace('{{highest_score}}', round($generalSummary["highest_score"], 2), $html);
            $html = str_replace('{{highest_scorer_name}}', $generalSummary["highest_scorer_name"], $html);
            $html = str_replace('{{highest_scorer_id}}', $generalSummary["highest_scorer_id"], $html);
            $html = str_replace('{{total_questions}}', $generalSummary["total_questions"], $html);

            $html = str_replace('{{reportDetails}}', $reportDetailsHtml, $html);

            $html = str_replace('{{reportNFR}}', $reportNFRHtml, $html);

            $html = str_replace('{{year}}', $year, $html);
            $html = str_replace('{{imagen}}', $imagenBase64, $html);

            $fileName = ($language == 'es' ? 'REPORTE-SALA' : 'REPORT-ROOM') . '-' . $generalSummary["game_room_code"] . '-' . date('Y-m-d_H-i-s') . '.pdf';

            $conn->commit();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
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
