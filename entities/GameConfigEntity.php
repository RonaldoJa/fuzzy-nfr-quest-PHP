<?php

class GameConfigEntity
{
    public $language;
    public $additional_context;

    public function __construct($language, $additional_context)
    {
        $this->language = $language;
        $this->additional_context = $additional_context;
    }
}
