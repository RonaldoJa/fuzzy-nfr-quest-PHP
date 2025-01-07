<?php

require_once 'services/GameService.php';

class GameController
{
    public static function prepareGameContent()
    {
        $courseId = $_GET['params'][0];
        return GameService::prepareGameContent($courseId);  
    }
}
