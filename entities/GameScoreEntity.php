<?php

class GameScoreEntity
{
    public $game_room_id;
    public $user_id;
    public $score;
    public $duration;
    public $answered_questions;

    public function __construct($game_room_id, $user_id, $score, $duration, $answered_questions)
    {
        $this->game_room_id = $game_room_id;
        $this->user_id = $user_id;
        $this->score = $score;
        $this->duration = $duration;
        $this->answered_questions = $answered_questions;
    }
}
