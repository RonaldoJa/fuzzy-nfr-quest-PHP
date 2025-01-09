<?php

class GameRoomEntity
{
    public $code;
    public $user_id_created;
    public $expiration_date;

    public function __construct($code, $user_id_created, $expiration_date)
    {
        $this->code = $code;
        $this->user_id_created = $user_id_created;
        $this->expiration_date = $expiration_date;
    }
}
