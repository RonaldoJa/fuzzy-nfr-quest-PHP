<?php
// serve the banco-requerimientos-template.xlsx file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="banco-requerimientos-template.xlsx"');
readfile('banco-requerimientos-template.xlsx');
?>